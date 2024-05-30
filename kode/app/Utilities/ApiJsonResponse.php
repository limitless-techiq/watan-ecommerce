<?php


namespace App\Utilities;

use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;
use function response;
use Illuminate\Support\Facades\Cache;
class ApiJsonResponse
{
    protected int $httpCode = 200;
    protected int $code = 20000;
    protected string $message;
    protected mixed $data;
    protected ?string $details = '';
    protected array $headers = [];

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public function success(string $message = '', int $httpCode = Response::HTTP_OK, ?int $code = null): JsonResponse
    {

        $this->httpCode = $httpCode;

        /**
         * @var int $code
         */
        $this->code = $code ?? $httpCode * 100;
        $this->message = $message;
        $currency_data = Cache::get('currency_data');
        if($currency_data){
            $currency = $currency_data;
        }
        else{
            $currency =  Currency::where('default','1')->first();
        }
        $response = [
            'status' => 'SUCCESS',
            'code' => $this->code,
            'message' => translate($message),
            'locale' => app()->getLocale(),
            'currency' => new CurrencyResource( $currency),
            'data' => $this->data
        ];

        return response()->json($response, $this->httpCode, $this->headers);
    }

    public function fails(string $message = '', int $httpCode = Response::HTTP_BAD_REQUEST, ?int $code = null): JsonResponse
    {
        $this->httpCode = $httpCode;

        /**
         * @var int $code
         */
        $this->code = $code ?? $httpCode * 100;
        $this->message = $message;
    
        $response = [
            'status' => 'ERROR',
            'code' => $this->code,
            'message' =>translate($message),
            'locale' => app()->getLocale(),
            'data'  =>  count($this->data) > 0 ?  $this->data : (object)[]
        ];

        return response()->json($response, $this->httpCode, $this->headers);
    }
}
