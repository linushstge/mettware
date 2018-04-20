{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_content_left"}{/block}

{block name="frontend_index_content"}
    {if $mettwoch.mwOrderStop}
        {include file="frontend/_includes/messages.tpl" type="success" content="Bestellungen sind geperrt für heute"}
    {/if}

    {if $mettwoch.mwOrderReset}
        {include file="frontend/_includes/messages.tpl" type="success" content="Bestellungen sind für heute wieder freigeschaltet"}
    {/if}

    {if $mettwoch.mwOrderResetFailure}
        {include file="frontend/_includes/messages.tpl" type="error" content="Sie haben keine Berechtigung die Mett Bestellungen für heute wieder frei zu geben"}
    {/if}

    <div class="panel has--border" style="margin-top: 30px">
        <div class="panel--title primary is--underline">
            Mettwoch Bestellungen für{if $mettwoch.shippingDate|date_format:'d.m.Y' == $smarty.now|date_format:'d.m.Y'} Heute, {/if} den {$mettwoch.shippingDate|date_format:'d.m.Y'}
        </div>
        <div class="panel--body is--rounded">

            <div class="panel--table">
                <div class="orders--table-header panel--tr">
                    <div class="panel--th column--date">Bestellnummer</div>

                    <div class="panel--th column--id">Anzahl</div>

                    <div class="panel--th column--dispatch">Bestellsumme</div>

                    <div class="panel--th column--status">Name</div>

                    <div class="panel--th column--actions">Status</div>
                </div>

                {foreach $mettwoch.orders as $order}
                    <div class="order--item panel--tr">
                        <div class="order--date panel--td column--date">
                            <div class="column--label">
                                column
                            </div>
                            <div class="column--value">
                                {$order->getNumber()}
                            </div>
                        </div>
                        <div class="order--date panel--td column--date">
                            <div class="column--label">
                                column
                            </div>
                            <div class="column--value">
                                {foreach $order->getDetails() as $details}
                                    {if $details->getMode() === 0}
                                        {$details->getQuantity()}x {$details->getArticleName()}
                                        <br/>
                                    {/if}
                                {/foreach}
                            </div>
                        </div>
                        <div class="order--date panel--td column--date">
                            <div class="column--label">
                                column
                            </div>
                            <div class="column--value">
                                {$order->getInvoiceAmount()|currency}
                            </div>
                        </div>
                        <div class="order--date panel--td column--date">
                            <div class="column--label">
                                column
                            </div>
                            <div class="column--value">
                                {$order->getBilling()->getFirstName()} {$order->getBilling()->getLastName()}
                            </div>
                        </div>
                        <div class="order--date panel--td column--date">
                            <div class="column--label">
                                column
                            </div>
                            <div class="column--value is--align-center">

                                {if $order->getPaymentStatus()->getId() == 12}
                                    <i class="icon--check"></i> Bezahlt
                                {else}
                                    <i class="icon--cross"></i> Zahlung ausstehend
                                {/if}
                            </div>
                        </div>
                    </div>
                {/foreach}

            </div>

        </div>
        <div class="panel--body is--wide">
            {$productsTotal = 0}
            {foreach $mettwoch.quantityTotal as $totalQuantities}
                <h3>Anzahl: {$totalQuantities.quantity}x {$totalQuantities.name}</h3>
                {$productsTotal = $productsTotal + $totalQuantities.quantity}
            {/foreach}
            <h3>Gesamtanzahl: {$productsTotal}</h3>
            <h3>Gesamtsumme: {$mettwoch.sumAmount|currency}</h3>
        </div>
        <div class="panel--body is--wide">
            <form action="{url controller="mettwochorder"}" method="post">


                <div class="block-group">
                    <div class="block" style="width:50%;">
                        <label for="shippingDate">Lieferdatum:</label>
                        <input class="datepicker" data-datepicker="true" name="shippingDate" type="text" data-defaultDate="{$smarty.now|date_format:"%Y-%m-%d"}"/>
                        <input type="submit" class="btn is--primary" value="Liste anzeigen">

                    </div>
                    <div class="block" style="width:50%; text-align: right">
                        <a class="btn" style="" href="{url controller=mettwochorder action=stopOrders}">Bestellungen stoppen</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
{/block}