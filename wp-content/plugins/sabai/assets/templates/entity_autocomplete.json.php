<?php
$arr = array('items' => array(), 'total' => 0);
if (!empty($entities)) {
    foreach ($entities as $entity) {
        $arr['items'][] = array(
            'id' => $entity->getId(),
            'text' => Sabai::h($entity->getTitle()),
        );
    }
    $arr['total'] = count($arr['items']);
}
echo json_encode($arr);