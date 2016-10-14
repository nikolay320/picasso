<?php
class Sabai_Addon_Content_Model_PostGateway extends Sabai_Addon_Content_Model_Base_PostGateway
{    
    public function incrementView($postId)
    {
        $sql = sprintf(
            'UPDATE %scontent_post SET post_views = post_views + 1 WHERE post_id IN (%s)',
            $this->_db->getResourcePrefix(),
            implode(',', array_map('intval', (array)$postId))
        );
        $this->_db->exec($sql);
    }
    
    public function slugExists($bundle, $slug, $postId = null)
    {
        $sql = sprintf(
            'SELECT COUNT(*) FROM %scontent_post WHERE post_entity_bundle_name = %s AND post_slug = %s %s',
            $this->_db->getResourcePrefix(),
            $this->_db->escapeString($bundle->name),
            $this->_db->escapeString($slug),
            !isset($postId) ? '' : 'AND post_id != ' . intval($postId)
        );
        return $this->_db->query($sql)->fetchSingle() > 0;
    }
    
    public function countUserFavorites($userId, $bundles)
    {
        $sql = sprintf(
            'SELECT COUNT(*) FROM %1$svoting_vote v
               LEFT JOIN %1$scontent_post p ON p.post_id = v.vote_entity_id
               WHERE p.post_entity_bundle_name IN (%3$s) AND v.vote_tag = %2$s AND v.vote_user_id = %4$d AND p.post_status = %5$s',
             $this->_db->getResourcePrefix(),
             $this->_db->escapeString('favorite'),
             implode(',', array_map(array($this->_db, 'escapeString'), $bundles)),
             $userId,
             $this->_db->escapeString(Sabai_Addon_Content::POST_STATUS_PUBLISHED)
        );
        return $this->_db->query($sql)->fetchSingle();
    }
    
    public function fetchUserFavorites($userId, $bundles, $limit, $offset, $sort)
    {
        if ($sort === 'active') {
            $sql = sprintf(
                'SELECT v.vote_entity_id FROM %1$svoting_vote v
                   LEFT JOIN %1$scontent_post p ON p.post_id = v.vote_entity_id
                   LEFT JOIN %1$sentity_field_content_activity a ON a.entity_id = p.post_id
                   WHERE p.post_entity_bundle_name IN (%3$s) AND v.vote_tag = %2$s AND v.vote_user_id = %4$d AND p.post_status = %5$s
                   ORDER BY a.active_at DESC',
                $this->_db->getResourcePrefix(),
                $this->_db->escapeString('favorite'),
                implode(',', array_map(array($this->_db, 'escapeString'), $bundles)),
                $userId,
                $this->_db->escapeString(Sabai_Addon_Content::POST_STATUS_PUBLISHED)
            );
        } else {
            $sql = sprintf(
                'SELECT v.vote_entity_id FROM %1$svoting_vote v
                   LEFT JOIN %1$scontent_post p ON p.post_id = v.vote_entity_id
                   WHERE p.post_entity_bundle_name IN (%3$s) AND v.vote_tag = %2$s AND v.vote_user_id = %4$d AND p.post_status = %5$s
                   ORDER BY %6$s DESC',
                $this->_db->getResourcePrefix(),
                $this->_db->escapeString('favorite'),
                implode(',', array_map(array($this->_db, 'escapeString'), $bundles)),
                $userId,
                $this->_db->escapeString(Sabai_Addon_Content::POST_STATUS_PUBLISHED),
                $sort === 'added' ? 'v.vote_created' : 'p.post_published' 
            );
        }        
        $rs = $this->_db->query($sql, $limit, $offset);
        $ret = array();
        while ($row = $rs->fetchRow()) {
            $ret[] = (int)$row[0];
        }
        return $ret;
    }
}