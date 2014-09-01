<?php
/**
 * User: dongww
 * Date: 14-5-24
 * Time: 上午11:05
 */

namespace Dongww\SilexBase\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;

class UploadFileHelper
{
    const GROUPED_BY_NONE  = 0;
    const GROUPED_BY_DATE  = 1;
    const GROUPED_BY_MONTH = 2;
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
     * 文件上传后的分组方式
     *
     * @var
     */
    protected $groupedBy;

    /**
     * @var Filesystem
     */
    protected $fs;

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

    /**
     * @param string $baseUploadUrl
     */
    public function setBaseUploadUrl($baseUploadUrl)
    {
        $this->baseUploadUrl = rtrim($baseUploadUrl, '/') . '/';

    }

    public function __construct($uploadDir, $baseUploadUrl, $groupedBy = self::GROUPED_BY_NONE)
    {
        $this->setUploadDir($uploadDir);
        $this->setBaseUploadUrl($baseUploadUrl);
        $this->groupedBy = $groupedBy;

        $this->fs = new FileSystem();
    }

    /**
     * 上传单个文件
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param  bool                                                $autoName
     * @return null|string
     */
    public function uploadFile(UploadedFile $file, $autoName = true)
    {
//print_r($file);exit;
        if ($autoName) {
            $filename = sprintf('%s.%s', uniqid(), $file->getClientOriginalExtension());
        } else {
            $filename = $file->getClientOriginalName();
        }

        $relativePath = '';
        switch ($this->groupedBy) {
            case static::GROUPED_BY_MONTH:
                $dateObj      = new \DateTime();
                $relativePath = $dateObj->format('Y-m') . '/';
                break;
            case static::GROUPED_BY_DATE:
                $dateObj      = new \DateTime();
                $relativePath = $dateObj->format('Y-m') . '/' . $dateObj->format('d') . '/';
                break;
            default:
        }

        $toDir = $this->getUploadDir() . $relativePath;

        if (!$this->fs->exists($toDir)) {
            $this->fs->mkdir($toDir);
        }

        $file->move($toDir, $filename);

        return $relativePath . $filename;
    }

    /**
     * 上传多个文件
     *
     * @param  Request  $request
     * @param  string   $fieldName
     * @param  bool     $autoName
     * @return string[]
     */
    public function uploadFiles(Request $request, $fieldName, $autoName = true)
    {
        /** @var UploadedFile[] $files */
        $files     = $request->files->get($fieldName);
        $fileNames = [];

        foreach ($files as $file) {
            if ($file) {
                $fileNames[] = $this->uploadFile($file, $autoName);
            } else {
                continue;
            }
        }

        return $fileNames;
    }

    /**
     * 获取图片的文件路径
     *
     * @param  string $fileName
     * @return string
     */
    public function getRealPath($fileName)
    {
        return realpath($this->uploadDir . $fileName);
    }

    /**
     * 获取图片链接
     *
     * @param  string $fileName
     * @param  string $pre      前缀，一个加前缀的文件例如：small_abc.jpg
     * @return string
     */
    public function getUrl($fileName, $pre = '')
    {
        return $this->baseUploadUrl . $pre . $fileName;
    }

    /**
     * 移除文件
     *
     * @param string $fileName 文件名，例如：abc.jpg、2014-05-06/abc.jpg
     * @param string $pre      前缀，一个加前缀的文件例如：small_abc.jpg
     */
    public function remove($fileName, $pre = '')
    {
        $fileName = $this->getUploadDir() . $pre . $fileName;
        if ($this->fs->exists($fileName)) {
            $this->fs->remove($fileName);
        }
    }
}
