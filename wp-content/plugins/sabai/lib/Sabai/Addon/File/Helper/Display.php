<?php
class Sabai_Addon_File_Helper_Display extends Sabai_Helper
{
    public function help(Sabai $application, $file, $size = null)
    {
        // Try fetching stream before sending out headers
        $fp = $application->getAddon('File')->getStorage()->fileStorageGetStream($file['name'], $size);

        if (!$file['is_image']) {
            if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');
            header('Cache-Control: must-revalidate');
            header('Content-Disposition: attachment; filename="' . str_replace(array("\r", "\n", '"'), '', $file['title']) . '"');
            header('Content-Description: File Transfer');
        } else {
            $cache_limit = time() + 432000; // 5 days
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', $cache_limit));
            header('Cache-Control: must-revalidate, max-age=' . $cache_limit);
            header('Content-Disposition: inline; file_name="' . str_replace(array("\r", "\n", '"'), '', $file['title']) . '"');
        }
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $file['updated']));
        header('Content-Type: ' . $file['type']);
        //if ($file['size']) header('Content-Length: ' . $file['size']);

        while(@ob_end_clean());
        while (!feof($fp)) echo fgets($fp, 2048);
        fclose($fp);
    }
}