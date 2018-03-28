<?php

class UpdatePriceProductAsia
{
    /**
     * @use command.php
     */
    public function run()
    {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        echo "1. Dang cap gia ...at: " . now() . PHP_EOL;
        $write->query(" 
                    UPDATE catalog_product_entity_decimal d 
                    LEFT JOIN catalog_product_entity pro ON pro.entity_id = d.entity_id
                    AND d.attribute_id = 75
                    AND d.entity_type_id = 4
                    INNER JOIN catalog_product_entity_varchar wh_sku_att ON pro.entity_id = wh_sku_att.entity_id
                    AND wh_sku_att.entity_type_id = 4
                    AND wh_sku_att.attribute_id = 334
                    AND wh_sku_att.store_id = 0
                    INNER JOIN `asia_response_api_update_temp` b ON wh_sku_att.`value` = b.sku
                    SET d.`VALUE` = b.price;

              ");

        echo "2. het hang neu hieu luc not in bao gia --> sua thanh het hang neu hieu luc/ reviewing not in bao gia : " . now() . PHP_EOL;
        $write->query("
                    UPDATE catalog_product_entity_int PRO_INT
                        JOIN catalog_product_entity_int PRO_STATUS ON PRO_INT.entity_id = PRO_STATUS.entity_id
                        AND PRO_STATUS.entity_type_id = 4
                        AND PRO_STATUS.attribute_id = 96
                        AND PRO_STATUS.`value` IN (0, 1)
                        JOIN catalog_product_entity pro ON PRO_INT.entity_id = pro.entity_id
                        AND PRO_INT.entity_type_id = 4
                        AND PRO_INT.attribute_id = (
                            SELECT
                                attribute_id
                            FROM
                                eav_attribute e
                            WHERE
                                e.entity_type_id = 4
                            AND e.attribute_code = 'instock'
                        )
                        SET PRO_INT.`value` = 3
                        WHERE
                            PRO_INT.entity_id NOT IN (
                                SELECT
                                    wh_sku_att.entity_id
                                FROM
                                    catalog_product_entity_varchar wh_sku_att
                                INNER JOIN asia_response_api_update_temp b ON wh_sku_att.`value` = b.sku
                                AND wh_sku_att.entity_type_id = 4
                                AND wh_sku_att.attribute_id = 334
                                AND wh_sku_att.store_id = 0
                            )
                        AND pro.type_id = 'simple';

              ");

        echo "3.set instock = 3 neu asia_status =2,5 va dang hieu luc/ review, con lai thi set instock=asis_status: " . now() . PHP_EOL;
        $write->query("
        UPDATE catalog_product_entity_int PRO_INT
        JOIN catalog_product_entity_int PRO_STATUS ON PRO_INT.entity_id = PRO_STATUS.entity_id
        AND PRO_STATUS.entity_type_id = 4
        AND PRO_STATUS.attribute_id = 96
        JOIN catalog_product_entity pro ON PRO_INT.entity_id = pro.entity_id
        AND PRO_INT.entity_type_id = 4
        AND PRO_INT.attribute_id = (
            SELECT
                attribute_id
            FROM
                eav_attribute e
            WHERE
                e.entity_type_id = 4
            AND e.attribute_code = 'instock'
        )
        JOIN catalog_product_entity_varchar wh_sku_att ON pro.entity_id = wh_sku_att.entity_id
        AND wh_sku_att.entity_type_id = 4
        AND wh_sku_att.attribute_id = 334
        AND wh_sku_att.store_id = 0
        INNER JOIN asia_response_api_update_temp b ON wh_sku_att.`value` = b.sku
        SET PRO_INT.`value` = (
            CASE
            WHEN b. STATUS IN (2, 5)
            AND PRO_STATUS.`value` IN (0, 1) THEN
                3
            ELSE
                b. STATUS
            END
        )
        WHERE
            pro.type_id = 'simple'
        AND PRO_INT.`value` <> (
            CASE
            WHEN b. STATUS IN (2, 5)
            AND PRO_STATUS.`value` IN (0, 1) THEN
                3
            ELSE
                b. STATUS
            END
        );
        
       ");
        echo "4.hieu  luc sp trong bao gia va co anh va ko co gia at: " . now() . PHP_EOL;
        $write->query("
        UPDATE catalog_product_entity_int PRO_INT
        SET `value` = 1
        WHERE
            PRO_INT.entity_id IN (
                SELECT
                    pro.entity_id
                FROM
                    catalog_product_entity pro
                INNER JOIN catalog_product_entity_varchar wh_sku_att ON pro.entity_id = wh_sku_att.entity_id
                AND wh_sku_att.entity_type_id = 4
                AND wh_sku_att.attribute_id = 334
                AND wh_sku_att.store_id = 0
                INNER JOIN `asia_response_api_update_temp` b ON wh_sku_att.`value` = b.sku
                AND b. STATUS IN (1, 3, 4, 6)
                WHERE
                    pro.type_id = 'simple'
            )
        AND PRO_INT.entity_id IN (
            SELECT
                entity_id
            FROM
                catalog_product_entity_varchar v
            WHERE
                v.entity_type_id = 4
            AND attribute_id = 85
            AND `value` <> 'no_selection'
        )
        AND PRO_INT.entity_id NOT IN (
            SELECT
                entity_id
            FROM
                catalog_product_entity_decimal d
            WHERE
                d.entity_type_id = 4
            AND attribute_id = 76
            AND `value` IS NOT NULL
        )
        AND PRO_INT.entity_type_id = 4
        AND PRO_INT.attribute_id = 96
        AND PRO_INT.`value` <> 1;
        
       ");
        echo "5.review sp trong bao gia va ko co anh .at: " . now() . PHP_EOL;
        $write->query("
        UPDATE catalog_product_entity_int PRO_INT
        SET `value` = 0
        WHERE
            PRO_INT.entity_id IN (
                SELECT
                    pro.entity_id
                FROM
                    catalog_product_entity pro
                INNER JOIN catalog_product_entity_varchar wh_sku_att ON pro.entity_id = wh_sku_att.entity_id
                AND wh_sku_att.entity_type_id = 4
                AND wh_sku_att.attribute_id = 334
                AND wh_sku_att.store_id = 0
                INNER JOIN `asia_response_api_update_temp` b ON wh_sku_att.`value` = b.sku
                AND b. STATUS IN (1, 3, 4, 6)
                WHERE
                    pro.type_id = 'simple'
            )
        AND (
            PRO_INT.entity_id NOT IN (
                SELECT
                    entity_id
                FROM
                    catalog_product_entity_varchar v
                WHERE
                    v.entity_type_id = 4
                AND attribute_id = 85
            )
            OR PRO_INT.entity_id IN (
                SELECT
                    entity_id
                FROM
                    catalog_product_entity_varchar v
                WHERE
                    v.entity_type_id = 4
                AND attribute_id = 85
                AND `value` = 'no_selection'
            )
        )
        AND PRO_INT.entity_type_id = 4
        AND PRO_INT.attribute_id = 96
        AND PRO_INT.`value` <> 0;
        ");



        echo "Index Product Price ... at: " . now() . PHP_EOL;

        $process = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_price');

        $process->reindexAll();

        echo "Index Product Flat ... at: " . now() . PHP_EOL;

        $flat = Mage::getModel('index/indexer')->getProcessByCode('catalog_product_flat');

        $flat->reindexAll();

        echo "Done" . now() . PHP_EOL;
    }
}