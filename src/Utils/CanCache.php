<?php

use Illuminate\Support\Facades\Cache;

trait CanCache
{
    public ?int $timeInSeconds = null;
    public ?int $timeInMinutes = null;
    public ?int $timeInHours = 6;

    public function storeCache(Request $request, $data): void
    {
        $cacheKey = $this->cacheKey($request);
        $time = 300;
        if ($this->timeInSeconds) {
            $time = $this->timeInSeconds;
        }
        if ($this->timeInMinutes) {
            $time = $this->timeInMinutes * 60;
        }
        if ($this->timeInHours) {
            $time = $this->timeInHours * 3600;
        }
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, $data, $time);
        }
    }

    public function cacheKey(Request $request): string
    {
        return config('laravel-api-auth.cache.cache-prefix') . md5($request->fullUrlWithQuery(method_exists($request, 'safe') ? $request->safe()->toArray() : $request->all()));
    }

    public function checkIfCacheKeyExists(Request $request): bool
    {
        return Cache::has($this->cacheKey($request));
    }

    /**
     * @throws \Exception
     */
    public function getCacheData(Request $request)
    {
        $key = $this->cacheKey($request);
        if (!Cache::has($key)) {
            throw new \Exception('CacheKey Not Found');
        }
        return Cache::get($this->cacheKey($request));
    }
}
