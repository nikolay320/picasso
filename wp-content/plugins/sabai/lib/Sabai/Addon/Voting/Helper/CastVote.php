<?php
class Sabai_Addon_Voting_Helper_CastVote extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $tag, $values, array $options = array())
    {
        $settings = $application->Voting_TagSettings($tag);
        
        $default = array(
            'comment' => '',
            'system' => false,
            'user_id' => null,
            'reference_id' => null, // required for edit/delte vote
            'delete' => false,
            'edit' => false,
            'auto_calculated' => false,
        );
        $options += $default;
        
        $new_values = $prev_values = array();
        
        if (!is_array($values)) {
            $values = array('' => $values);
            $return_single = true;
        }

        // If not a system vote...
        if (!$options['system']) {
            
            // Get user ID
            $user_id = isset($options['user_id']) ? $options['user_id'] : $application->getUser()->id;
            
            // Require a valid ip address for anonymous votes
            if (empty($user_id) && empty($options['reference_id']) && (!$ip = $this->_getIp())) {
                throw new Sabai_RuntimeException(__('You do not have the permission to perform this action.', 'sabai'));
            }

            foreach ($values as $name => $value) {
                if (!$options['auto_calculated'] || strlen($name)) {
                    $this->_validateVoteValue($application, $settings, $value);
                }
                $this->_validateVotePermission($application, $settings, $entity, $tag, $value);
            }
            
            if (empty($settings['allow_multiple']) || !empty($options['reference_id'])) {
                $votes = $application->getModel('Vote', 'Voting')
                    ->entityType_is($entity->getType()) 
                    ->entityId_is($entity->getId())
                    ->userId_is($user_id)
                    ->tag_is($tag)
                    ->name_in(array_keys($values));
                if (!empty($options['reference_id'])) {
                    $votes->referenceId_is($options['reference_id']);
                }
                if (!empty($ip)) {
                    $votes->ip_is($ip);
                }
                $votes = $votes->fetch('created', 'DESC')->getArray(null, 'name');
                
                foreach ($values as $name => $value) {
                    $new_values[$name] = $value;
                    if (isset($votes[$name])) {
                        // Has voted before
                        $prev_values[$name] = $votes[$name]->value;
                        if ($votes[$name]->value == $value && !$options['edit']) {
                            // Same value, undo vote
                            $votes[$name]->markRemoved();
                            $new_values[$name] = false;
                        } else {
                            // Update vote
                            $votes[$name]->value = $value;
                        }
                    } elseif (!$options['delete']) {
                        $prev_values[$name] = false;
                        // New vote
                        $votes[$name] = $this->_createVote($application, $entity, $tag, $value, $user_id, $options['comment'], $name, $options['reference_id']);
                    }
                }
            } else {
                foreach ($values as $name => $value) {
                    $new_values[$name] = $value;
                    $prev_values[$name] = false;
                    // New vote
                    $votes[$name] = $this->_createVote($application, $entity, $tag, $value, $user_id, $options['comment'], $name);
                }
            }
        } else {
            foreach ($values as $name => $value) {
                if (!$options['auto_calculated'] || strlen($name)) {
                    $this->_validateVoteValue($application, $settings, $value);
                }
                $this->_validateVotePermission($application, $settings, $entity, $tag, $value);
            }
            
            // Voting cast by the system
            foreach ($values as $name => $value) {                
                $new_values[$name] = $value;
                $prev_values[$name] = false;
                // New vote
                $votes[$name] = $this->_createVote($application, $entity, $tag, $value, 0, $options['comment'], $name, $options['reference_id']);
            }
        }
        
        $application->getModel(null, 'Voting')->commit();
        
        // Calculate results and update entity
        $results = $application->getAddon('Voting')->recalculateEntityVotes($entity, $tag);
        
        foreach ($votes as $name => $vote) {
            if (!isset($results[$name])) {
                $results[$name] = array('count' => 0, 'sum' => '0.00', 'last_voted_at' => 0);
            }
            $results[$name]['value'] = $new_values[$name];
            $results[$name]['prev_value'] = $prev_values[$name];
            $result = $results[$name];
        
            // Notify voted
            $application->Action('voting_entity_voted', array($entity, $tag, $result));
            // Notify by vote tag, entity type, and bundle
            $application->Action('voting_entity_voted_' . $tag, array($entity, $result, $vote));
            $application->Action('voting_' . $entity->getType() . '_entity_voted_' . $tag, array($entity, $result, $vote));
            $application->Action('voting_' . $entity->getType() . '_' . $entity->getBundleType() . '_entity_voted_' . $tag, array($entity, $result, $vote));
        }
        
        return empty($return_single) ? $results : $results[''];
    }
    
    private function _validateVoteValue(Sabai $application, array $settings, $value)
    {
        // Validate value
        if (!is_numeric($value)
            || $value > $settings['max']
            || $value < $settings['min']
            || intval(strval($value * 100)) % intval(strval($settings['step'] * 100)) !== 0 // avoid using float numbers for % operation
            || (empty($value) && !$settings['allow_empty'])
        ) {
            throw new Sabai_UnexpectedValueException('Invalid vote value: ' . (string)$value);
        }
    }
    
    private function _validateVotePermission(Sabai $application, array $settings, Sabai_Addon_Entity_IEntity $entity, $tag, $value)
    {        
        // Require additional permission to down vote
        if ($value < 0
            && $settings['require_vote_permissions']
            && $settings['require_vote_down_permission']
            && !$application->HasPermission($entity->getBundleName() . '_voting_down_' . $tag)
        ) {
            throw new Sabai_RuntimeException(__('You do not have the permission to perform this action.', 'sabai'));
        }
    }
    
    private function _createVote(Sabai $application, Sabai_Addon_Entity_IEntity $entity, $tag, $value, $userId, $comment, $name, $referenceId = null)
    {
        $vote = $application->getModel(null, 'Voting')->create('Vote')->markNew();
        $vote->entity_type = $entity->getType();
        $vote->entity_id = $entity->getId();
        $vote->bundle_id = $application->Entity_Bundle($entity)->id;
        $vote->tag = $tag;
        $vote->user_id = $userId;
        $vote->value = $value;
        $vote->comment = $comment;
        $vote->name = $name;
        $vote->reference_id = $referenceId;
        $vote->ip = $this->_getIp();
        
        return $vote;
    }
    
    private function _getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
            if (!empty($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        return '';
    }
}