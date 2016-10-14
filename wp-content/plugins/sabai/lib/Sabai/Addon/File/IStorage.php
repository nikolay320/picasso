<?php
interface Sabai_Addon_File_IStorage
{
    /**
     * Saves data to the storage as an object.
     * @param string $name Unique identifier of the object
     * @param string $content File content
     * @param array $options
     * @throws Sabai_IException
     */
    public function fileStoragePut($name, $content, array $options);
    /**
     * Gets the stream resource of a stored object.
     * @param string $name Unique identifier of the object
     * @param string $size Image size, being either one of large|medium or null for original size. Applicable to image files only.
     * @return resource
     * @throws Sabai_IException
     */
    public function fileStorageGetStream($name, $size = null);
    /**
     * Gets the URL of a stored object.
     * @param string $size Image size, being either one of large|medium or null for original size. Applicable to image files only.
     * @return string
     */
    public function fileStorageGetUrl($name, $size = null);
    /**
     * Gets the URL of a stored object thumbnail.
     * @param string $name Unique identifier of the object
     * @return string
     */
    public function fileStorageGetThumbnailUrl($name);
    /**
     * Removes a stored object.
     * @param string $name Unique identifier of the object
     * @throws Sabai_IException
     */
    public function fileStorageDelete($name);
}