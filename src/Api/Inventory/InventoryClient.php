<?php

namespace Taler\Api\Inventory;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\Inventory\Actions\GetCategories;
use Taler\Api\Inventory\Actions\GetCategory;
use Taler\Api\Inventory\Dto\CategoryListResponse;
use Taler\Api\Inventory\Dto\CategoryProductList;
use Taler\Exception\TalerException;

class InventoryClient extends AbstractApiClient
{
    /**
     * @param array<string, string> $headers Optional request headers
     * @return CategoryListResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategories(array $headers = []): CategoryListResponse|array
    {
        return GetCategories::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategoriesAsync(array $headers = []): mixed
    {
        return GetCategories::runAsync($this, $headers);
    }

    /**
     * @param int $categoryId
     * @param array<string, string> $headers Optional request headers
     * @return CategoryProductList|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategory(int $categoryId, array $headers = []): CategoryProductList|array
    {
        return GetCategory::run($this, $categoryId, $headers);
    }

    /**
     * @param int $categoryId
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getCategoryAsync(int $categoryId, array $headers = []): mixed
    {
        return GetCategory::runAsync($this, $categoryId, $headers);
    }
}


