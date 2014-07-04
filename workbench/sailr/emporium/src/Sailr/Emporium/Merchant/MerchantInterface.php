<?php
namespace Sailr\Emporium\Merchant;
interface MerchantInterface {
    public function isProductPublic(\Item $item);
    public function setApiMode($mode);
    public function getApiMode();
}