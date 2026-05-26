<?php

namespace app\models;

use yii\base\Model;
use yii\imagine\Image;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg, webp'],
        ];
    }

    public function upload($dir): string | false
    {
        if ($this->validate()) {
            $path = md5(uniqid()) . '.' . $this->imageFile->extension;
            $this->imageFile->saveAs($dir . '/' . $path);
            return $path;
        } else {
            return false;
        }
    }

    public function uploadPreview($dir): string | false
    {
        if ($this->validate()) {
            $path_preview = md5(uniqid()) . '.jpg';

            Image::thumbnail($this->imageFile->tempName, 400, null)
                ->save($dir . '/' . $path_preview, ['format' => 'jpg', 'quality' => 80]);

            return $path_preview;
        }
        return false;
    }
}
