{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_checkout_confirm_tos_panel"}
    {$smarty.block.parent}
    <div class="tos--panel panel has--border">
        <div class="panel--title primary is--underline">
            <label for="shippingDate">Lieferdatum:</label>
        </div>
        <div class="panel--body is--wide">
            <input id="shippingDate" type="date" name="shippingDate" value="{$smarty.now|date_format:"%Y-%m-%d"}">
        </div>
    </div>
{/block}