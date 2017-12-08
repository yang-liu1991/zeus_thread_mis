<?php
/**
 * Author: young_liu@vip.sina.com
 * Created Time: 2016-12-30 10:29:31
 */

namespace common\models;

use Yii;
use yii\web\AssetManager;

class ThreadAssetManager extends AssetManager
{
	protected function hash($path)
    {
        if (is_callable($this->hashCallback)) {
            return call_user_func($this->hashCallback, $path);
        }
        #$path = (is_file($path) ? dirname($path) : $path) . filemtime($path);
        $path = is_file($path) ? dirname($path) : $path;
        return sprintf('%x', crc32($path . Yii::getVersion()));
    }
}

# vim: set noexpandtab ts=4 sts=4 sw=4 :
