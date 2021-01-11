<?php

namespace App\Http\Controllers;

use Exception;
use External\Bar\Exceptions\ServiceUnavailableException as ServiceUnavailableExceptionAlias;
use External\Bar\Movies\MovieService;
use External\Baz\Exceptions\ServiceUnavailableException as ServiceUnavailableExceptionBaz;
use External\Baz\Movies\MovieService as MovieServiceBaz;
use External\Foo\Exceptions\ServiceUnavailableException;
use External\Foo\Movies\MovieService as MovieServiceFoo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getTitles(Request $request): JsonResponse
    {
        $allTitle = $this->getAllTitle();

        return response()->json($allTitle);
    }

    /**
     * @return JsonResponse
     */
    public function getAllTitle(): JsonResponse
    {
        try {
            $titleWithBar = $this->getTitleWithBar();
            $titleWithBaz = $this->getTitleWithBaz();
            $titleWithFoo = $this->getTitleWithFoo();
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failure',
            ]);
        }
        $returnValue = $this->array_flatten(array_merge($titleWithBar, $titleWithBaz, $titleWithFoo));

        return response()->json([
            implode(', ', $returnValue)
        ]);
    }

    function array_flatten($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->array_flatten($value));
            } else {
                $result = array_merge($result, array($value));
            }
        }
        return $result;
    }

    /**
     * @return string[]
     *
     * @throws ServiceUnavailableExceptionAlias
     */
    private function getTitleWithBar(): array
    {
        $titles = (new MovieService)->getTitles();
        dump($this->array_flatten($titles['titles']));

        return $this->array_flatten($titles['titles']);
    }

    /**
     * @return array
     *
     * @throws ServiceUnavailableExceptionBaz
     */
    private function getTitleWithBaz(): array
    {
        $titles = (new MovieServiceBaz())->getTitles();

        return array_values($titles['titles']);
    }

    /**
     * @return string[]
     *
     * @throws ServiceUnavailableException
     */
    private function getTitleWithFoo(): array
    {
        $titles = (new MovieServiceFoo())->getTitles();
        return array_values($titles);
    }
}
