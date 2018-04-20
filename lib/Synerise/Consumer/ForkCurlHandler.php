<?php
namespace Synerise\Consumer;


use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use GuzzleHttp\Stream\Stream;

/**
 * Ring handler that returns a canned response or evaluated function result.
 */
class ForkCurlHandler
{
    /** @var callable|array|FutureArrayInterface */
    private $result;

    /**
     * Provide an array or future to always return the same value. Provide a
     * callable that accepts a request object and returns an array or future
     * to dynamically create a response.
     *
     * @param array|FutureArrayInterface|callable $result Mock return value.
     */
    public function __construct($result)
    {
        $this->result = $result;
    }

    public function __invoke(array $request)
    {

        Core::doSleep($request);
        $response = is_callable($this->result)
            ? call_user_func($this->result, $request)
            : $this->result;

        $data = "'".(Stream::factory($request['body']))."'";
    
        $headers = '';
        foreach($request['headers'] as $key=>$val){ 
            $headers .= '-H "'.$key.': '.$val[0].'" ';
        }
    

        $exec = 'curl -X POST '.trim($headers).' -d ' . $data . ' "' . $request['url'] . '"';
        $exec .= " >/dev/null 2>&1 &";
        exec($exec, $output, $return_var);


        if (is_array($response)) {
            $response = new CompletedFutureArray($response + [
                'status'        => 200,
                'body'          => null,
                'headers'       => array(),
                'reason'        => null,
                'effective_url' => null,
            ]);
        } elseif (!$response instanceof FutureArrayInterface) {
            throw new \InvalidArgumentException(
                'Response must be an array or FutureArrayInterface. Found '
                . Core::describeType($request)
            );
        }

        return $response;
    }
}
