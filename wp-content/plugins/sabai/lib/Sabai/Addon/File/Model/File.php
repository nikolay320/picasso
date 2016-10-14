<?php
class Sabai_Addon_File_Model_File extends Sabai_Addon_File_Model_Base_File
{
    public function getHumanReadableSize()
    {
        if ($this->size >= 1048576) return sprintf(__('%.1fMB', 'sabai'), $this->size / 1048576);
        if ($this->size >= 1024) return sprintf(__('%.1fKB', 'sabai'), $this->size / 1024);
        return sprintf(__('%dB', 'sabai'), $this->size);
    }

    public function unlink()
    {
        $this->_model->File_Delete($this->name);
    }

    public function toArray()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'size' => $this->size,
            'type' => $this->type,
            'is_image' => $this->is_image,
            'width' => $this->width,
            'height' => $this->height,
            'extension' => $this->extension,
            'size_hr' => $this->getHumanReadableSize(),
            'user_id' => $this->user_id,
            'updated' => $this->updated,
        );
    }
}

class Sabai_Addon_File_Model_FileRepository extends Sabai_Addon_File_Model_Base_FileRepository
{
}