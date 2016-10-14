<?php
class Sabai_Helper_CsvFile extends Sabai_Helper
{    
    public function help(Sabai $application, $filePath, $toUtf8 = false, $toCRLF = false, $unlink = true)
    {
        if (false === $fp = fopen($filePath, 'r')) {
            if ($unlink) @unlink($filePath);
            throw new Sabai_RuntimeException(sprintf('An error occurred while opening the CSV file %s.', $filePath));
        }
        if ($toUtf8 || $toCRLF) {
            $contents = fread($fp, filesize($filePath));
            fclose($fp);
            if ($contents === false) {
                if ($unlink) @unlink($filePath);
                throw new Sabai_RuntimeException(sprintf('An error occurred while reading the CSV file %s.', $filePath));
            }
            if (false === $fp = fopen($filePath, 'w+')) {
                if ($unlink) @unlink($filePath);
                throw new Sabai_RuntimeException(sprintf('An error occurred while opening the CSV file %s with write permission.', $filePath));
            }
            if ($toCRLF) {
                $contents = strtr($contents, array("\r" => "\r\n", "\n" => "\r\n"));
            }
            if ($toUtf8) {
                if (false !== $from_encoding = mb_detect_encoding($contents)) {
                    $contents = mb_convert_encoding($contents, 'UTF-8', $from_encoding);
                }
            }
            if (false === fwrite($fp, $contents)) {
                if ($unlink) @unlink($filePath);
                throw new Sabai_RuntimeException(sprintf('An error occurred while writing to the CSV file %s.', $filePath));
            }
            rewind($fp);
        }
        return $fp;
    }
}