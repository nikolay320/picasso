<?php
$arr = array('items' => array(), 'total' => 0);
if (!empty($identities)) {
    foreach ($identities as $identity) {
        $arr['items'][] = array(
            'id' => $identity->id,
            'text' => Sabai::h($identity->name),
            'username' => $identity->username,
            'gravatar' => $this->GravatarUrl($identity->email, Sabai::THUMBNAIL_SIZE_SMALL, $identity->gravatar_default, $identity->gravatar_rating),
        );
    }
    $arr['total'] = count($arr['items']);
}
echo json_encode($arr);