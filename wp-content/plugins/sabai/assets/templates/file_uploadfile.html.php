<?php
if (isset($error)) {
    echo json_encode(array('error' => Sabai::h($error)));
    return;
}
$file_arr = array('success' => 1);
foreach ($files as $file) {
    $file = $file->toArray();
    if ($file['is_image']) {
        $file['thumbnail'] = $this->File_ThumbnailUrl($file['name']);
    }
    $file['icon'] = $this->File_Icon($file['extension']);
    $file_arr['files'][] = array_map(array('Sabai', 'h'), $file);
}
echo json_encode($file_arr);