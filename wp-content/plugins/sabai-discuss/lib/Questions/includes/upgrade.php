<?php
function sabai_addon_questions_upgrade(Sabai $application, $previousVersion)
{
    if (version_compare($previousVersion, '1.1.0dev322', '<')) {            
        $db = $application->getDB();
        $db->begin();
        // Alter some tables
        $sql = sprintf(
            'ALTER TABLE %sentity_field_questions_closed
               ADD closed_at INT(10) UNSIGNED NOT NULL,
               ADD closed_by INT(10) UNSIGNED NOT NULL,
               MODIFY COLUMN value TINYINT(1) UNSIGNED NOT NULL;',
            $db->getResourcePrefix()
        );
        $db->exec($sql);
        $sql = sprintf(
            'ALTER TABLE %sentity_field_questions_resolved
               ADD resolved_at INT(10) UNSIGNED NOT NULL,
               MODIFY COLUMN value TINYINT(1) UNSIGNED NOT NULL;',
            $db->getResourcePrefix()
        );
        $db->exec($sql);
        $sql = sprintf(
            'ALTER TABLE %sentity_field_content_trashed
               MODIFY COLUMN prev_status VARCHAR(20) NOT NULL;',
            $db->getResourcePrefix()
        );
        $db->exec($sql);
        // delete resolved entreis with value 0
        $sql = sprintf(
            'DELETE FROM %sentity_field_questions_resolved WHERE value = 0',
            $db->getResourcePrefix()
        );
        $db->exec($sql);
        $sql = sprintf(
            'UPDATE %sentity_field_questions_resolved SET resolved_at = %d',
            $db->getResourcePrefix(),
            time()
        );
        $db->exec($sql);
        // delete closed entreis with value 0
        $sql = sprintf(
            'DELETE FROM %sentity_field_questions_closed WHERE value = 0',
            $db->getResourcePrefix()
        );
        $db->exec($sql);
        $sql = sprintf(
            'UPDATE %sentity_field_questions_closed SET closed_at = %d',
            $db->getResourcePrefix(),
            time()
        );
        $db->exec($sql);
        // taxonomy_terms table is now questions_tags table
        $sql = sprintf(
            'INSERT INTO %1$sentity_field_questions_tags (entity_type, bundle_id, entity_id, weight, value)
               SELECT entity_type, bundle_id, entity_id, weight, value FROM %1$sentity_field_taxonomy_terms',
            $db->getResourcePrefix()
        );
        $db->exec($sql);
        // Update status
        foreach (array(1 => 'published', 2 => 'pending', 3 => 'trashed') as $status_num => $status_str) {
            $sql = sprintf(
                'UPDATE %1$scontent_post SET post_status = %2$s WHERE post_status = %3$d',
                $db->getResourcePrefix(),
                $db->escapeString($status_str),
                $status_num
            );
            $db->exec($sql);
            $sql = sprintf(
                'UPDATE %1$sentity_field_content_trashed SET prev_status = %2$s WHERE prev_status = %3$d',
                $db->getResourcePrefix(),
                $db->escapeString($status_str),
                $status_num
            );
            $db->exec($sql);
        }
        // Generate slugs
        $sql = sprintf(
            'SELECT post_id, post_title FROM %scontent_post WHERE post_entity_bundle_name = %s',
            $db->getResourcePrefix(),
            $db->escapeString('questions')
        );
        $rs = $db->query($sql);
        while ($row = $rs->fetchRow()) {
            $sql = sprintf(
                'UPDATE %scontent_post SET post_slug = %s WHERE post_id = %d',
                $db->getResourcePrefix(),
                $db->escapeString($application->Slugify($row[1])),
                $row[0]
            );
            $db->exec($sql);
        }
        // Insert bundle type
        $sql = sprintf(
            'SELECT bundle_name, bundle_type FROM %sentity_bundle WHERE bundle_entitytype_name = %s',
            $db->getResourcePrefix(),
            $db->escapeString('content')
        );
        $rs = $db->query($sql);
        while ($row = $rs->fetchRow()) {
            $sql = sprintf(
                'UPDATE %scontent_post SET post_entity_bundle_type = %s WHERE post_entity_bundle_name = %s',
                $db->getResourcePrefix(),
                $db->escapeString($row[1]),
                $db->escapeString($row[0])
            );
            $db->exec($sql);
        }
        $sql = sprintf(
            'SELECT bundle_name, bundle_type FROM %sentity_bundle WHERE bundle_entitytype_name = %s',
            $db->getResourcePrefix(),
            $db->escapeString('taxonomy')
        );
        $rs = $db->query($sql);
        while ($row = $rs->fetchRow()) {
            $sql = sprintf(
                'UPDATE %staxonomy_term SET term_entity_bundle_type = %s WHERE term_entity_bundle_name = %s',
                $db->getResourcePrefix(),
                $db->escapeString($row[1]),
                $db->escapeString($row[0])
            );
            $db->exec($sql);
        }         
        $db->commit();
            
        if ($field = $application->getModel('FieldConfig', 'Entity')->name_is('taxonomy_terms')->fetch()->with('Fields')->getFirst()) {
            $field->markRemoved();
            $removed_fields= array($field->name => $field);
            $application->getAddon('Entity')->deleteFieldStorage($removed_fields);
            $application->Action('entity_delete_field_configs_success', array($removed_fields));
            $application->getModel(null, 'Entity')->commit();
        }
            
        @unlink($application->getAddonPath('Content') . '/assets/css/admin.css');
        @unlink($application->getAddonPath('Comment') . '/assets/css/admin.css');
        @unlink($application->getAddonPath('Taxonomy') . '/assets/css/admin.css');
        @unlink($application->getAddonPath('Taxonomy') . '/assets/css/main.css');
            
        $application->Action('system_clear_cache', array($log = new ArrayObject()));
    }
}
