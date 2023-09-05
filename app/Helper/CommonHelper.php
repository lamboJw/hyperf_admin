<?php

namespace App\Helper;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Log;
use App\Model\OpAction;
use App\Service\TokenService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Context\ApplicationContext;

class CommonHelper
{
    public function del_zip_dir($path)
    {
        $file_list = scandir($path);
        foreach ($file_list as $file) {
            if (in_array($file, ['.', '..'])) continue;
            $file_path = rtrim($path, '/') . "/{$file}";
            if (is_dir($file_path)) {
                $this->del_zip_dir($file_path);
            } else {
                @unlink($file_path);
            }
        }
        rmdir($path);
    }

    public function op_log($path, $res)
    {
        // 从操作行为表获取数据
        $opAction = \Hyperf\Context\ApplicationContext::getContainer()->get(OpAction::class);
        $action = $opAction->getOneByOneCondition('path', $path);
        // 没在操作行为表中的则不记录
        if (empty($action)) {
            return;
        }
        $op = $action['name'];
        // 获取 jwt token
        $tokenService = \Hyperf\Utils\ApplicationContext::getContainer()->get(TokenService::class);
        $name = $tokenService->getUsername();
        if ($name) {
            // 操作状态
            if ($res['code'] == 0) {
                $status = 1;
                if (!empty($action['desc'])) {  //如果有详细描述，把详细描述添加到操作内容后面
                    if (preg_match_all('/\{(.*?)}/', $action['desc'], $match)) {    //如果描述中有占位符，根据占位符获取返回结果中的内容填充到占位符中
                        foreach ($match[1] as $k => $v) {
                            $field_arr = explode('.', $v);
                            $value = $res['result'];
                            foreach ($field_arr as $field) {
                                if (isset($value[$field])) {
                                    $value = $value[$field];
                                } else {
                                    $value = '';
                                    break;
                                }
                            }
                            $action['desc'] = str_replace($match[0][$k], $value, $action['desc']);
                        }
                    }
                    $op .= ' ' . $action['desc'];
                }
            } else {
                $status = 0;
            }
        } else {
            $status = 0;
        }
        // 插入操作日志
        $data = ['op' => $op, 'status' => $status, 'name' => $name];
        $log = ApplicationContext::getContainer()->get(Log::class);
        $log->createOne($data);
    }

    function makeRequest($method, $url, $params = [], $content_type = "application/x-www-form-urlencoded", $expire = 5, $is_browser = true, $extend = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0');
        $_header = [
            'Accept-Language: zh-cn',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache',
        ];
        $method = strtoupper($method);
        if ($method === "GET") {
            if (!empty($params)) {
                $url .= (stripos($url, '?') !== false) ? '&' : '?';
                $url .= (is_string($params)) ? $params : http_build_query($params, '', '&');
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        } elseif ($method === "POST") {
            switch ($content_type) {
                case "application/x-www-form-urlencoded":
                    curl_setopt($ch, CURLOPT_POST, true);
                    if (true === $is_browser) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    } else {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    }
                    break;
                case "application/json":
                    if (is_array($params)) {
                        $params = json_encode($params);
                    }
                    $_header[] = 'Content-Type: application/json';
                    $_header[] = 'Content-Length: ' . strlen($params);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    break;
                case 'application/xml':
                {
                    curl_setopt($ch, CURLOPT_POST, true);
                    $_header[] = 'Content-Type: application/xml; charset=utf-8';
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                }
                case 'multipart/form-data':
                {
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    $_header[] = 'Content-Type: multipart/form-data';
                }
                default:
                    break;
            }
        } else {
            return false;
        }

        if (strpos($url, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $_header);

        if ($expire > 0) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $expire);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $expire);
        }

        if (!empty($extend)) {
            curl_setopt_array($ch, $extend);
        }

        $result['result'] = curl_exec($ch);
        $result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['url'] = $url;
        if ($result['result'] === false) {
            $result['result'] = curl_error($ch);
            $result['code'] = -curl_errno($ch);
            $result['url'] = $url;
        }

        curl_close($ch);
        return $result;
    }

    function toChineseNumber($money)
    {
        $money = round($money, 2);
        $cnynums = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        $cnyunits = ['元', '角', '分'];
        $cnygrees = ['拾', '佰', '仟', '万', '拾', '佰', '仟', '亿'];
        list($int, $dec) = explode('.', $money, 2);
        $dec = array_filter([$dec[1], $dec[0]]);
        $ret = array_merge($dec, [implode('', $this->cnyMapUnit(str_split($int), $cnygrees)), '']);
        $ret = implode('', array_reverse($this->cnyMapUnit($ret, $cnyunits)));
        return str_replace(array_keys($cnynums), $cnynums, $ret);
    }

    private function cnyMapUnit($list, $units)
    {
        $ul = count($units);
        $xs = array();
        foreach (array_reverse($list) as $x) {
            $l = count($xs);
            if ($x != '0' || !($l % 4))
                $n = ($x == '0' ? '' : $x) . ($units[($l - 1) % $ul]);
            else $n = is_numeric($xs[0][0]) ? $x : '';
            array_unshift($xs, $n);
        }
        return $xs;
    }


    /**
     * 获取客户端ip地址
     * @return mixed
     */
    public function ip()
    {
        $res = ApplicationContext::getContainer()->get(RequestInterface::class)->getServerParams();
        if (isset($res['http_client_ip'])) {
            return $res['http_client_ip'];
        } elseif (isset($res['http_x_real_ip'])) {
            return $res['http_x_real_ip'];
        } elseif (isset($res['http_x_forwarded_for'])) {
            //部分CDN会获取多层代理IP，所以转成数组取第一个值
            $arr = explode(',', $res['http_x_forwarded_for']);
            return $arr[0];
        } else {
            return $res['remote_addr'];
        }
    }

    public function deleteExpiredFile($base_dir, $expire_time = 86400, $frequency = 3600)
    {
        if (!is_dir($base_dir)) {
            return;
        }
        //清除太长时间前的文件
        $time = time();
        $last_time_file = "{$base_dir}/last_gc_time";
        $fp = fopen($last_time_file, !file_exists($last_time_file) ? 'w+' : 'r+');
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return;
        }
        $last_time = fgets($fp);
        rewind($fp);
        if ($time - $last_time >= $frequency) { //每小时删除一次
            $file_list = scandir($base_dir);
            foreach ($file_list as $file) {
                if (in_array($file, ['.', '..'])) continue;
                $file_time = filemtime($base_dir . '/' . $file);
                if ($time - $file_time > $expire_time) {
                    @unlink($base_dir . '/' . $file);
                }
            }
            fwrite($fp, $time);
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 过滤数组中不需要的key
     * @param array $arr
     * @param array $keys 要处理的keys
     * @param bool $keep_keys 是要保留还是删除keys中的key，true为保留，false为删除
     * @return array
     */
    public function filterArrayKey(array $arr, array $keys, bool $keep_keys = true): array
    {
        return array_filter($arr, function ($k) use ($keys, $keep_keys) {
            return $keep_keys ? in_array($k, $keys) : !in_array($k, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function convPdf($path): string
    {
        $path = BASE_PATH . '/storage/uploads' . $path;
        $ext = pathinfo($path)['extension'];
        if (!in_array(strtolower($ext), ['doc', 'docx'])) {
            throw new BusinessException(ErrorCode::CONV_PDF_EXT_INVALID);
        }
        $pdf_dir = pathinfo($path)['dirname'] . '/conv_pdf/';
        if (!is_dir($pdf_dir)) {
            mkdir($pdf_dir);
        }
        $pdf_path = $pdf_dir . str_replace('.' . $ext, '.pdf', basename($path));
        if (file_exists($pdf_path)) {
            return str_replace(BASE_PATH . '/storage/uploads', '', $pdf_path);
        }
        if (!file_exists($path)) {
            throw new BusinessException(ErrorCode::CONV_PDF_FILE_NOT_EXISTS);
        }
        if (DIRECTORY_SEPARATOR == '\\') {    //本地win环境
            $uno_path = 'D:\LibreOffice\program';     //LibreOffice/program路径
            putenv('UNO_PATH=' . $uno_path);
            $unoconv = "{$uno_path}\python.exe {$uno_path}\unoconv";
            $exec = sprintf('%s -f pdf "%s" -o "%s"', $unoconv, $path, $pdf_path);
        } else {
            $uno_path = '/opt/libreoffice/program';     //LibreOffice/program路径
            $exec = "{$uno_path}/soffice --headless --invisible --convert-to pdf:writer_pdf_Export '{$path}' --outdir '{$pdf_dir}' '-env:UserInstallation=file:///tmp/LibreOffice_Conversion_\${USER}'";
        }
        exec($exec, $output, $code);
        if (!file_exists($pdf_path)) {
            throw new BusinessException(ErrorCode::CONV_PDF_FAIL);
        }
        return str_replace(BASE_PATH . '/storage/uploads', '', $pdf_path);
    }
}
