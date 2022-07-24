<?php
return [
        [
                'module' => 'sale',
                'event' => '\Bitrix\Catalog\StoreProductTable::onBeforeUpdate',
                'class' => '\Sdvv\Antistore\Stores',
                'method' => 'updateAmount'
            ],
    ];
