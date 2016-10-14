<?php
class Sabai_Addon_Comment_Model_PostGateway extends Sabai_Addon_Comment_Model_Base_PostGateway
{
    public function getFeaturedByEntities(array $entityIds)
    {
        $sql = sprintf('SELECT * FROM %scomment_post WHERE post_entity_id IN (%s) AND post_status = %d',
            $this->_db->getResourcePrefix(),
            implode(',', $entityIds),
            Sabai_Addon_Comment::POST_STATUS_FEATURED
        );

        return $this->_db->query($sql);
    }
    
    public function getCountByEntities(array $entityIds, $includeHidden = false)
    {
        $sql = sprintf('SELECT post_entity_id, COUNT(*) FROM %scomment_post WHERE post_entity_id IN (%s) %s GROUP BY post_entity_id',
            $this->_db->getResourcePrefix(),
            implode(',', $entityIds),
            $includeHidden ? '' : 'AND post_status != ' . Sabai_Addon_Comment::POST_STATUS_HIDDEN
        );
        $rs = $this->_db->query($sql);
        $ret = array();
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]] = (int)$row[1];
        }
        return $ret;
    }
    
    public function updateFeaturedByEntity($entityId, $featuredCommentCount = 5)
    {
        // Fetch top voted posts
        $sql = sprintf('SELECT post_id FROM %scomment_post WHERE post_entity_id = %d AND post_status != %d ORDER BY post_vote_sum DESC, post_published_at DESC',
            $this->_db->getResourcePrefix(),
            $entityId,
            Sabai_Addon_Comment::POST_STATUS_HIDDEN
        );
        $posts_to_feature = $this->_db->query($sql, $featuredCommentCount, 0)->fetchAllColumns(0);
        
        // Reset the status of all posts but those hidden to published
        $sql = sprintf('UPDATE %scomment_post SET post_status = %d WHERE post_entity_id = %d AND post_status != %d',
            $this->_db->getResourcePrefix(),
            Sabai_Addon_Comment::POST_STATUS_PUBLISHED,
            $entityId,
            Sabai_Addon_Comment::POST_STATUS_HIDDEN
        );
        $this->_db->exec($sql);
        
        if (empty($posts_to_feature)) return;
        
        // Set the status of top voted posts as featured
        $sql = sprintf('UPDATE %scomment_post SET post_status = %d WHERE post_entity_id = %d AND post_id IN (%s)',
            $this->_db->getResourcePrefix(),
            Sabai_Addon_Comment::POST_STATUS_FEATURED,
            $entityId,
            implode(',', $posts_to_feature)
        );
        $this->_db->exec($sql);
    }
    
    public function getCountByStatus($entityId = null)
    {
        if (isset($entityId)) {
            $sql = sprintf('SELECT post_status, COUNT(*) FROM %scomment_post WHERE post_entity_id = %d GROUP BY post_status',
                $this->_db->getResourcePrefix(),
                $entityId
            );
        } else {
            $sql = sprintf('SELECT post_status, COUNT(*) FROM %scomment_post GROUP BY post_status', $this->_db->getResourcePrefix());
        }
        $ret = array();
        $rs = $this->_db->query($sql);
        while ($row = $rs->fetchRow()) {
            $ret[$row[0]] = $row[1];
        }
        return $ret;
    }
}