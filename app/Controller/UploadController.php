<?php


namespace App\Controller;

use App\Exception\BusinessException;
use App\Helper\CommonHelper;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Support\Filesystem\Filesystem;
use function \Hyperf\Coroutine\co;

#[AutoController]
class UploadController extends AbstractController
{

    #[Inject]
    protected Filesystem $filesystem;


    // 上传合同附件
    public function index()
    {
        set_time_limit(0);
        $file = $this->request->file('file');
        $fileType = $this->request->input('type');
        $isRealName = $this->request->input('isRealName');
        $filePath = 'uploads/';
        $filePath .= match ($fileType) {
            "file_db" => "file_db/",
            default => "tmp/",
        };
        // 加上月份
        $currentMonth = date('Ym');
        $filePath .= $currentMonth . "/";
        $lowExtension = strtolower($file->getExtension());
        if ($isRealName) {
            $fileName = str_replace('.' . $file->getExtension(), '', $file->getClientFilename()) . "." . $lowExtension;
        } else {
            $fileName = md5($file->getClientFilename() . time()) . "." . $lowExtension;
        }
        $success = true;
        if (!$this->_isExists($filePath . $fileName)) {
            $success = $this->_upload($filePath . $fileName);
        }

        if (!$success) {
            throw new BusinessException(1, '上传失败');
        }

        if ($this->request->input('isZip') == 1) {
            $this->_imageCropper(BASE_PATH . '/storage/' . $filePath . $fileName);
        }

        return $this->success(['filename' => $fileName, 'dirname' => str_replace('uploads', '', $filePath) . $fileName]);
    }

    private function _upload($dir): bool
    {
        $this->_saveFile($dir);
        if (!$this->_isExists($dir)) { //不存在再保存一次
            $this->_saveFile($dir);
        }
        return $this->_isExists($dir);
    }

    public function _saveFile($dir)
    {
        $file = $this->request->file('file');
        $stream = fopen($file->getRealPath(), 'r+');
        $this->filesystem->put(
            $dir,
            $stream
        );
    }

    private function _isExists($dir): bool
    {
        return file_exists(BASE_PATH . '/storage/' . $dir);
    }

    /*缩小图片*/
    private function _imageCropper($source_path)
    {
        co(function () use ($source_path) {
            $source_info = getimagesize($source_path);
            $source_mime = $source_info['mime'];
            $percent = 0.4;//缩小比例
            list($width, $height) = getimagesize($source_path);
            $new_width = $width * $percent;
            $new_height = $height * $percent;

            switch ($source_mime) {
                case 'image/gif':
                    $source_image = imagecreatefromgif($source_path);
                    break;

                case 'image/jpeg':
                    $source_image = imagecreatefromjpeg($source_path);
                    break;

                case 'image/png':
                    $source_image = imagecreatefrompng($source_path);
                    break;

                default:
                    return false;
                    break;
            }
            $target_image = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            @unlink($source_path);
            imagejpeg($target_image, $source_path);
            imagedestroy($target_image);
            return true;
        });
    }

    public function preview_doc(): array
    {
        $path = $this->request->input('path');
        $pdf_path = $this->container->get(CommonHelper::class)->convPdf($path);
        return parent::success(compact('pdf_path'));
    }
}
