{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_content_left"}{/block}

{block name="frontend_index_content"}
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
                                {$quantity = 0}
                                {foreach $order->getDetails() as $details}
                                    {$quantity = $quantity + $details->getQuantity()}
                                {/foreach}
                                {$quantity} Brötchen
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
            <h3>Anzahl Brötchen: {$mettwoch.quantityTotal}</h3>
            <h3>Gesamtsumme: {$mettwoch.sumAmount|currency}</h3>
        </div>
        <div class="panel--body is--wide">
            <form action="{url controller="mettwochorder"}" method="post">
                <label for="shippingDate">Lieferdatum:</label> <input id="shippingDate" type="date" name="shippingDate" value="{$mettwoch.shippingDate}">
                <input type="submit" class="btn is--primary" value="Liste anzeigen">
            </form>
        </div>
    </div>
{/block}