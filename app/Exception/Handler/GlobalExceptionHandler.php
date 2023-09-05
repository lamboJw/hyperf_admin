<?php


namespace App\Exception\Handler;

use App\Constants\ErrorCode;
use App\Event\RequestDone;
use App\Exception\BusinessException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Validation\ValidationException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * 业务异常处理器
 */
class GlobalExceptionHandler extends ExceptionHandler {

    protected \Psr\Log\LoggerInterface $logger;

    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponseInterface $response) {
        // 阻止异常冒泡
        $this->stopPropagation();
        $res = array();

        if ($throwable instanceof ValidationException) {// 参数校验失败
            $body = $throwable->validator->errors()->first();
            $res['code'] = ErrorCode::INVALID_PARAMS;
            $res['msg'] = $body;
            $res['data'] = [];

        } elseif ($throwable instanceof BusinessException) {// 自定义抛出的异常
            $res['code'] = $throwable->getCode();
            $res['msg'] = $throwable->getMessage();
            $res['data'] = [];
        } elseif ($throwable instanceof TokenValidException) {// Token 校验出错
            echo $throwable->getCode();
            echo $throwable->getMessage();
            $res['code'] = ErrorCode::AUTH_TOKEN_INVALID;
            $res['msg'] = ErrorCode::getMessage($res['code']);
            $res['data'] = [];
        } else {
            $res['code'] = ErrorCode::UNKNOWN_ERROR;
            $res['msg'] = ErrorCode::getMessage($res['code']);
            $res['data'] = ['error' => $throwable->getMessage()];
        }

        // 记录日志
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error(sprintf("request uri : %s", $this->request->getRequestUri()));
        if($this->request->getMethod() == "GET"){
            $this->logger->error(sprintf("request params : %s", json_encode($this->request->getQueryParams())));
        }else{
            $this->logger->error(sprintf("request params : %s", json_encode($this->request->getParsedBody())));
        }

        // 触发事件
        // 请求成功
        $this->eventDispatcher->dispatch(new RequestDone($res));

        return $response->withStatus(400)
            ->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withBody(new SwooleStream(json_encode($res, JSON_UNESCAPED_UNICODE)));
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool {
        return true;
    }
}
