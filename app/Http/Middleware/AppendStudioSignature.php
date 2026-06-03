<?php

namespace App\Http\Middleware;

use App\Support\Studio;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @studio daniel-od
 */
class AppendStudioSignature
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (Studio::headers() as $name => $value) {
            $response->headers->set($name, $value);
        }

        if (
            $response instanceof JsonResponse
            && $request->is('api/*')
            && $response->getStatusCode() >= 200
            && $response->getStatusCode() < 300
        ) {
            $data = $response->getData(true);

            if (is_array($data) && ! array_key_exists('_studio', $data)) {
                $data['_studio'] = Studio::meta();
                $response->setData($data);
            }
        }

        return $response;
    }
}
