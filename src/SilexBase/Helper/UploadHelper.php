<?php
/**
 * User: dongww
 * Date: 14-5-24
 * Time: 上午11:05
 */

namespace SilexBase\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UploadHelper
{
    /**
     * 文件上传目录
     *
     * @var string
     */
    protected $uploadDir;

    /**
     * 文件上传目录的Url路径
     *
     * @var string
     */
    protected $baseUploadUrl;

    /**
     * @return string
     */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * @param string $uploadDir
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = rtrim(rtrim($uploadDir, '/'), '\\') . '/';
    }

    function __construct($uploadDir)
    {
        $this->setUploadDir($uploadDir);
    }

    /**
     * 上传单个文件
     *
     * @param Request $request
     * @param $fieldName
     * @param bool $autoName
     * @return null|string
     */
    public function uploadFile(Request $request, $fieldName, $autoName = true)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get($fieldName);

        if ($autoName) {
            $filename = sprintf('%s.%s', uniqid(), $file->getClientOriginalExtension());
        } else {
            $filename = $file->getClientOriginalName();
        }

        $file->move($this->getUploadDir(), $filename);

        return $filename;
    }

    /**
     * 上传多个文件
     *
     * @param Request $request
     * @param $fieldName
     * @param bool $autoName
     * @return string[]
     */
    public function uploadFiles(Request $request, $fieldName, $autoName = true)
    {
        /** @var UploadedFile[] $files */
        $files     = $request->files->get($fieldName);
        $fileNames = array();

        foreach ($files as $file) {
            if ($file) {
                $fileNames[] = $this->uploadFile($request, $fieldName, $autoName);
            } else {
                continue;
            }
        }

        return $fileNames;
    }

    /**
     * 获取图片的文件路径
     *
     * @param $fileName
     * @return string
     */
    public function getRealPath($fileName)
    {
        return realpath($this->uploadDir . $fileName);
    }
}
