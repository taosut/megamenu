<?php
include(APP_PATH."/_header.php");

class gcafe_report{
	
	
	function getOrderReport($time){
		$db = new project_control();
		$db->set_db($GLOBALS['my_dbase']);

		$reportDate = date('Y-m-d', $time);
		$reportDate1 = date('Y-m-d', $time - 86400);
		$tb = "(select a.province, 
						orders, gmv/orders abs, gmv
						from (
						select default_name as province from directory_country_region
						where  country_id = 'VN'
						) a 
						left join (
						select d.region province, count(distinct c.entity_id) orders, sum(grand_total) gmv from  sales_flat_order c 
						join sales_flat_order_address d on c.shipping_address_id = d.entity_id
						where c.created_at >= '$reportDate1 17:00:00' and c.created_at <= '$reportDate 16:59:59' 
						and c.store_id not in (20,21,22,23)
						and c.status  not in  ('canceled','stock_transfer', 'delivery_failed')
						group by d.region) t2 on a.province = t2.province) t";
		
		$swhere = "where 1=1";
		//var_dump($tb);die();
		return $db->get_allrows($tb,$swhere,array());
	}


    function getOrderConfirmedReport($startDate, $endDate){
        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $tb = "(select a.province,
							orders, gmv/orders abs, gmv
							FROM (
							select default_name as province from directory_country_region
							where  country_id = 'VN'
							) a left join
							(
								select a.province, count(distinct a.entity_id) orders, sum(grand_total) gmv from 
								(select cast(left(c.increment_id, if(POSITION('-' IN c.increment_id) > 0,POSITION('-' IN c.increment_id)  - 1,length(c.increment_id))) as UNSIGNED) increment,
								c.grand_total, c.total_canceled, c.entity_id, d.region province from sales_flat_order c 
									join sales_flat_order_address d on c.shipping_address_id = d.entity_id
								where c.created_at > DATE_SUB('$startDate 17:00:00',INTERVAL 2 month) and c.created_at <= '$endDate 17:00:00'
								and c.store_id not in (20,21,22,23)
								and c.status not in ('canceled', 'stock_transfer', 'reject', 'delivery_failed', 'pending')
							) a join (select cast(left(a.increment_id, if(POSITION('-' IN a.increment_id) > 0,POSITION('-' IN a.increment_id)  - 1,length(a.increment_id))) as unsigned) increment, 
										min(b.created_at) created_at
										from sales_flat_order a join sales_flat_order_status_history b on a.entity_id = b.parent_id
										where a.store_id not in (20,21,22,23) and b.status in ( 'telephone_confirm' ,'pending_confirm')
										and b.created_at > DATE_SUB('$startDate 17:00:00',INTERVAL 2 month) and b.created_at <= '$endDate 17:00:00'
										group by cast(left(a.increment_id, if(POSITION('-' IN a.increment_id) > 0,POSITION('-' IN a.increment_id)  - 1,length(a.increment_id))) as unsigned)) b on
									a.increment = b.increment
							where b.created_at > '$startDate 17:00:00' and b.created_at <= '$endDate 17:00:00' 
							group by a.province) t2 on a.province = t2.province) t";

        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }


	function getProductReport($time, $GcafeProvince){
		$db = new project_control();
		$db->set_db($GLOBALS['my_dbase']);

		$reportDate = date('Y-m-d', $time);
		
		$tbWebsite = 'core_website';
		$tbWebsiteWhere = "where name = '$GcafeProvince'";
		$dataWebsite = $db->get_allrows($tbWebsite,$tbWebsiteWhere,array());
		//var_dump($dataWebsite);
		if(count($dataWebsite) == 0){
			return null;
		}
		$catTable = 'catalog_category_flat_store_' . $dataWebsite[0]['website_id'];
		
		$tb = "(select a.value name, count(distinct b.product_id) sku from catalog_category_entity_varchar a
					join catalog_category_product b on a.entity_id = b.category_id
					join catalog_product_entity c on b.product_id = c.entity_id
					join " . $catTable . " d on a.entity_id = d.entity_id
					join catalog_product_entity_int e on c.entity_id = e.entity_id and e.attribute_id = 96 and e.value = 1
					 where a.attribute_id = 41 and a.value in ('Chuột', 'Bàn phím', 'Tai nghe', 'Màn hình', 'Linh kiện máy tính', 'sản phẩm khác')
					and c.created_at <= '$reportDate 23:59:59'
					group by a.value) t";
		
		$swhere = "where 1=1";
		return $db->get_allrows($tb,$swhere,array());
	}
	
	function createIndex($table, $index_name, $index_field){
		$db = new project_control();
		$db->set_db($GLOBALS['my_dbase']);
		$db->create_index($table, $index_name, $index_field);
		die();
	}
	
	function getOrderReportByCat($time){
		$db = new project_control();
		$db->set_db($GLOBALS['my_dbase']);

		$reportDate = date('Y-m-d', $time);
		$reportDate1 = date('Y-m-d', $time - 86400);
		$tb = "(select a.value province, t1.unique_users, t1.views ,
						t2.orders, t2.gmv/t2.orders abs, t2.gmv from
						(
						select distinct (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end) value  from catalog_category_entity_varchar b
						join catalog_category_entity e on b.entity_id = e.entity_id and e.level > 1 and e.entity_id < 534
						where attribute_id = 41 and not  (b.value like '%Khuyến mãi%' or b.value like '%gcafe%' or b.value like '%KM%' or b.value like '%Mua%' or b.value like '%Giảm giá%')
						order by b.value
						) a left join 
				(select (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end) value, count(distinct customer_id) unique_users, count(customer_id) views from 
(select customer_id, category_id from
(
select e.customer_id ,
cast(substr(SUBSTR(b.url, instr(b.url, 'catalog/product/view/id/') + LENGTH('catalog/product/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'catalog/product/view/id/') + LENGTH('catalog/product/view/id/'), 5),'/') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'catalog/product/view/id/') + LENGTH('catalog/product/view/id/'), 5),'/') - 1 end
) as UNSIGNED) product_id
						from log_customer e join log_url a on a.visitor_id = e.visitor_id
						join log_url_info b on a.url_id = b.url_id
						where e.login_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
						and b.url like '%catalog/product/view/id/%'
) a join 
(select product_id, max(category_id) category_id  from 
catalog_category_product 
where category_id < 534 and category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%'  or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
group by product_id
) b on a.product_id = b.product_id
union all
select b.customer_id,
cast(substr(SUBSTR(b.url, instr(b.url, 'catalog/category/view/id/') + LENGTH('catalog/category/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'catalog/category/view/id/') + LENGTH('catalog/category/view/id/'), 5),'?') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'catalog/category/view/id/') + LENGTH('catalog/category/view/id/'), 5),'?') - 1 end) as UNSIGNED) category_id
 from 
(select e.customer_id, a.url_id, b.url
from log_customer e join log_url a on a.visitor_id = e.visitor_id
join log_url_info b on a.url_id = b.url_id
where e.login_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
and b.url like '%catalog/category/view/id%') b) a join (select distinct entity_id, value, attribute_id from  catalog_category_entity_varchar
where not (value like 'Khuyến mãi%' or value like '%KM%'  or value like '%Mua%' or value like '%Giảm giá%')
) b on a.category_id = b.entity_id and b.attribute_id = 41
group by (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end)

) t1 on a.value = t1.value
						left join
						(select (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end) value, sum(a.orders) orders, sum(a.gmv) gmv from
						(
						select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > '$reportDate1 17:00:00' and b.created_at < '$reportDate 17:00:00'  
						and b.store_id not in (20,21)
						and b.status not in ('canceled', 'stock_transfer', 'delivery_failed')
						group by a.order_id) b on a.order_id = b.order_id) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id < 534 and category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' 
						or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						where category_id <> 36
						) t
						group by catid) a join (select distinct entity_id, value, attribute_id from  catalog_category_entity_varchar) b on a.catid = b.entity_id and b.attribute_id = 41
						group by (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end)
						) t2 on t1.`value` = t2.value) t";
		
		$swhere = "where 1=1";
		//var_dump($tb);die();
		return $db->get_allrows($tb,$swhere,array());
	}


	function getCulmutiveOrderReport($time){
		$db = new project_control();
		$db->set_db($GLOBALS['my_dbase']);

		$firstDate = date('Y-m-01', $time);
		$reportDate = date('Y-m-d', $time);
		$tb = "(select a.province,
							orders, gmv/orders abs, gmv
							FROM (
							select default_name as province from directory_country_region
							where  country_id = 'VN'
							) a left join
							(select d.region province, count(distinct c.entity_id) orders, sum(grand_total) gmv from  sales_flat_order c 
							join sales_flat_order_address d on c.shipping_address_id = d.entity_id
							where c.created_at > DATE_SUB('$firstDate 17:00:00', INTERVAL 1 DAY) and c.created_at <= '$reportDate 17:00:00' 
						    and c.store_id not in (20,21)
							and c.status  not in  ('canceled','stock_transfer')
							group by d.region) t2 on a.province = t2.province
						) t";
		$swhere = "where 1=1";
		//var_dump($tb);die();
		return $db->get_allrows($tb,$swhere,array());
	}

	function getCulmutiveCatReport($time){
        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $firstDate = date('Y-m-01', $time);
        $reportDate = date('Y-m-d', $time);

	    $tb = "(select (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end) province, sum(a.orders) orders, sum(a.gmv) gmv, sum(a.gmv)/sum(a.orders) abs from
						(
						select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > DATE_SUB('$firstDate 17:00:00', INTERVAL 1 DAY) and b.created_at < '$reportDate 17:00:00' 
						and b.store_id not in (20,21) 
						and b.status not in ('canceled', 'stock_transfer', 'delivery_failed')
						group by a.order_id) b on a.order_id = b.order_id) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						where category_id <> 36
						) t
						group by catid) a join (select distinct entity_id, value, attribute_id from  catalog_category_entity_varchar) b on a.catid = b.entity_id and b.attribute_id = 41
						group by (case when b.value like '%mạng%' or b.value like '%switch%'  or b.value like '%wifi%' or b.value like '%router%' or b.value like '%modem%'
										or b.value like '%case%' or b.value like '%camera%' or b.value like '%Ổ cứng%' or b.value like '%Đồng hồ%' or b.value like '%PC nguyên bộ%'
										or b.value like '%Thực phẩm%' or b.value like '%Nước uống%' or b.value like '%Dịch vụ%' or b.value like '%tablet%' or b.value like '%bàn ghế%'
										or b.value like '%nguồn%' or b.value like '%combo%' or b.value like '%Bộ lưu điện%' or b.value like '%Điện thoại%' or b.value like '%linh kiện%'
										or b.value like '%tản nhiệt%' or b.value like '%Linh Phụ Kiện%' or b.value like '%laptop%' or b.value like '%loa%' or b.value like '%phụ kiện%'
										or b.value like '%Bàn di chuột%' or b.value like '%Thẻ Nhớ%'  or b.value like 'usb%'  or b.value like 'Sạc dự phòng%' or b.value like '%Thiết bị khác%'
										or b.value like '%Máy fax%' or b.value like '%Máy in%' then 'Sản phẩm khác'
										when b.value like '%Bo mạch chủ%' then 'Mainboard - Bo mạch chủ'
										when b.value like '%card màn hình%' then 'VGA - Card màn hình'
										when b.value like '%ram%' then 'Ram - Bộ nhớ trong'
										when b.value like '%vi xử lý%' then 'CPU - Vi xử lý'
										when b.value like 'màn hình%' then 'Màn hình máy tính'
										else b.value end)) t";
        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }


    function getSKUReportTekshopByCat($time){

            $db = new project_control();
            $db->set_db($GLOBALS['my_dbase']);

            $reportDate = date('Y-m-d', $time);
            $reportDate1 = date('Y-m-d', $time - 86400);
            $tb = "(select a.mapping_cat province, t1.unique_users, t1.views ,
						t2.orders, t2.gmv/t2.orders abs, t2.gmv from
(select distinct mapping_cat from catalog_category_mapping) a left join 
(select mapping_cat, count(distinct visitor_id) unique_users, count(visitor_id) views  from catalog_category_mapping a join
(select visitor_id, category_id from
(
select e.visitor_id ,
cast(substr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),'/') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),'/') - 1 end
) as UNSIGNED) product_id
						from log_visitor e join log_url a on a.visitor_id = e.visitor_id
						join log_url_info b on a.url_id = b.url_id
						where e.first_visit_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
						and b.url like '%tek/catalog/product/view/id/%'
) a join 
(select product_id, max(category_id) category_id  from 
catalog_category_product 
where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%'  or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
group by product_id
) b on a.product_id = b.product_id
union all
select b.visitor_id,
cast(substr(SUBSTR(b.url, instr(b.url, 'tek/catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),'?') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),'?') - 1 end) as UNSIGNED) category_id
 from 
(select e.visitor_id, a.url_id, b.url
from log_visitor e join log_url a on a.visitor_id = e.visitor_id
join log_url_info b on a.url_id = b.url_id
where e.first_visit_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
and b.url like '%tek/catalog/category/view/id%') b
) b on a.cat_id = b.category_id
group by mapping_cat) t1 on a.mapping_cat = t1.mapping_cat
left join 
(select mapping_cat, sum(b.orders) orders, sum(b.gmv) gmv  from catalog_category_mapping a join 
(select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > '$reportDate1 17:00:00' and b.created_at < '$reportDate 17:00:00' 
						and b.store_id in (20,21)
						and b.status not in ('canceled', 'stock_transfer', 'delivery_failed')
						group by a.order_id) b on a.order_id = b.order_id and a.parent_item_id is null) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' 
						or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						where category_id <> 36
						) t
						group by catid) b on a.cat_id = b.catid
group by mapping_cat) t2 on t1.mapping_cat = t2.mapping_cat
) t";

            $swhere = "where 1=1";
            //var_dump($tb);die();
            return $db->get_allrows($tb,$swhere,array());
        }


    function getCulmutiveSKUCatTekshopReport($time){
        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $firstDate = date('Y-m-01', $time);
        $reportDate = date('Y-m-d', $time);

        $tb = "(select b.mapping_cat province, sum(a.orders) orders, sum(a.gmv) gmv, sum(a.gmv)/sum(a.orders) abs from
                    (select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > DATE_SUB('$firstDate 17:00:00', INTERVAL 1 DAY) and b.created_at < '$reportDate 17:00:00' 
						and b.status not in ('canceled', 'stock_transfer')
						and b.store_id in (20,21)
						group by a.order_id) b on a.order_id = b.order_id and a.parent_item_id is null) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						where category_id <> 36
						) t
						group by catid) a join catalog_category_mapping b on a.catid = b.cat_id
                    group by b.mapping_cat) t";
        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }



    function getOrderReportTekshopByCat($time){

        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $reportDate = date('Y-m-d', $time);
        $reportDate1 = date('Y-m-d', $time - 86400);
        $tb = "(select a.mapping_cat province, t1.unique_users, t1.views ,
						t2.orders, t2.gmv/t2.orders abs, t2.gmv from
(select distinct mapping_cat from catalog_category_mapping) a left join 
(select mapping_cat, count(distinct visitor_id) unique_users, count(visitor_id) views  from catalog_category_mapping a join
(select visitor_id, category_id from
(
select e.visitor_id ,
cast(substr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),'/') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),'/') - 1 end
) as UNSIGNED) product_id
						from log_visitor e join log_url a on a.visitor_id = e.visitor_id
						join log_url_info b on a.url_id = b.url_id
						where e.first_visit_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
						and b.url like '%tek/catalog/product/view/id/%'
) a join 
(select product_id, max(category_id) category_id  from 
catalog_category_product 
where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%'  or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
group by product_id
) b on a.product_id = b.product_id
union all
select b.visitor_id,
cast(substr(SUBSTR(b.url, instr(b.url, 'tek/catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),'?') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),'?') - 1 end) as UNSIGNED) category_id
 from 
(select e.visitor_id, a.url_id, b.url
from log_visitor e join log_url a on a.visitor_id = e.visitor_id
join log_url_info b on a.url_id = b.url_id
where e.first_visit_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
and b.url like '%tek/catalog/category/view/id%') b
) b on a.cat_id = b.category_id
group by mapping_cat) t1 on a.mapping_cat = t1.mapping_cat
left join 
(select mapping_cat, sum(b.orders) orders, sum(b.gmv) gmv  from catalog_category_mapping a join 
(select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						1/item_count qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > '$reportDate1 17:00:00' and b.created_at < '$reportDate 17:00:00' 
						and b.store_id in (20,21)
						and b.status not in ('canceled', 'stock_transfer')
						group by a.order_id) b on a.order_id = b.order_id) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' 
						or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						join (select order_id, count(1) item_count, sum(row_total_incl_tax) purchase_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where a.created_at > '$reportDate1 17:00:00' and a.created_at < '$reportDate 17:00:00' 
						and b.store_id in (20,21)
						and b.status  not in  ('canceled','stock_transfer', 'delivery_failed')
						group by a.order_id) c on a.order_id = c.order_id
						where category_id <> 36
						) t
						group by catid) b on a.cat_id = b.catid
group by mapping_cat) t2 on t1.mapping_cat = t2.mapping_cat
) t";

        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }


    function getOrderConfirmedReportTekshopByCat($time){

        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $reportDate = date('Y-m-d', $time);
        $reportDate1 = date('Y-m-d', $time - 86400);
        $tb = "(select a.mapping_cat province, t1.unique_users, t1.views ,
						t2.orders, t2.gmv/t2.orders abs, t2.gmv from
(select distinct mapping_cat from catalog_category_mapping) a left join 
(select mapping_cat, count(distinct visitor_id) unique_users, count(visitor_id) views  from catalog_category_mapping a join
(select visitor_id, category_id from
(
select e.visitor_id ,
cast(substr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),'/') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/product/view/id/') + LENGTH('tek/catalog/product/view/id/'), 5),'/') - 1 end
) as UNSIGNED) product_id
						from log_visitor e join log_url a on a.visitor_id = e.visitor_id
						join log_url_info b on a.url_id = b.url_id
						where e.first_visit_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
						and b.url like '%tek/catalog/product/view/id/%'
) a join 
(select product_id, max(category_id) category_id  from 
catalog_category_product 
where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%'  or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
group by product_id
) b on a.product_id = b.product_id
union all
select b.visitor_id,
cast(substr(SUBSTR(b.url, instr(b.url, 'tek/catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),1, 
case when 
instr(SUBSTR(b.url, instr(b.url, 'catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),'?') = 0 then 5 ELSE
instr(SUBSTR(b.url, instr(b.url, 'tek/catalog/category/view/id/') + LENGTH('tek/catalog/category/view/id/'), 5),'?') - 1 end) as UNSIGNED) category_id
 from 
(select e.visitor_id, a.url_id, b.url
from log_visitor e join log_url a on a.visitor_id = e.visitor_id
join log_url_info b on a.url_id = b.url_id
where e.first_visit_at > '$reportDate1' and a.visit_time > '$reportDate1 17:00:00' and a.visit_time < '$reportDate 17:00:00'
and b.url like '%tek/catalog/category/view/id%') b
) b on a.cat_id = b.category_id
group by mapping_cat) t1 on a.mapping_cat = t1.mapping_cat
left join 
(select mapping_cat, sum(b.orders) orders, sum(b.gmv) gmv  from catalog_category_mapping a join 
(select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						1/item_count qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > '$reportDate1 17:00:00' and b.created_at < '$reportDate 17:00:00' 
						and b.store_id in (20,21)
						and b.status not in ('canceled', 'stock_transfer', 'reject', 'pending', 'no_product', 'delivery_failed')
						group by a.order_id) b on a.order_id = b.order_id) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' 
						or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						join (select order_id, count(1) item_count, sum(row_total_incl_tax) purchase_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where a.created_at > '$reportDate1 17:00:00' and a.created_at < '$reportDate 17:00:00' 
						and b.store_id in (20,21)
						and b.status not in ('canceled', 'stock_transfer', 'reject', 'pending', 'no_product', 'delivery_failed')
						group by a.order_id) c on a.order_id = c.order_id
						where category_id <> 36
						) t
						group by catid) b on a.cat_id = b.catid
group by mapping_cat) t2 on t1.mapping_cat = t2.mapping_cat
) t";

        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }

    function getCulmutiveCatTekshopReport($time){
        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $firstDate = date('Y-m-01', $time);
        $reportDate = date('Y-m-d', $time);

        $tb = "(select b.mapping_cat province, sum(a.orders) orders, round(sum(a.gmv)) gmv, sum(a.gmv)/sum(a.orders) abs from
                    (select catid, sum(qty_ordered) orders, sum(row_total) gmv from
						(select category_id catid, a.product_id, a.store_id, 
						1/item_count qty_ordered, row_total from 
						(select a.created_at, a.product_id, a.order_id, a.store_id, a.qty_ordered, row_total_incl_tax, a.row_total_incl_tax/b.total_non_campaign * b.grand_total 'row_total'
						from sales_flat_order_item a
						join (select b.increment_id, order_id, sum(row_total_incl_tax) 'total_non_campaign', grand_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where b.created_at > DATE_SUB('$firstDate 17:00:00', INTERVAL 1 DAY) and b.created_at < '$reportDate 17:00:00' 
						and b.status not in ('canceled', 'stock_transfer', 'reject', 'pending', 'no_product', 'delivery_failed')
						and b.store_id in (20,21)
						group by a.order_id) b on a.order_id = b.order_id ) a 
						join 
						(select product_id, max(category_id) category_id  from 
						catalog_category_product 
						where category_id not in  (select entity_id from  catalog_category_entity_varchar d where d.attribute_id = 41 and (d.value like '%Khuyến mãi%' or d.value like '%gcafe%' or d.value like '%KM%' or d.value like '%Mua%' or d.value like '%Giảm giá%'))
						group by product_id
						) b 
						on a.product_id = b.product_id
						join (select order_id, count(1) item_count, sum(row_total_incl_tax) purchase_total from 
						sales_flat_order_item a join sales_flat_order b on a.order_id = b.entity_id 
						where a.created_at > DATE_SUB('$firstDate 17:00:00', INTERVAL 1 DAY) and a.created_at < '$reportDate 17:00:00' 
						and b.store_id in (20,21)
						and b.status  not in  ('canceled', 'stock_transfer', 'reject', 'pending', 'no_product', 'delivery_failed')
						group by a.order_id) c on a.order_id = c.order_id
						where category_id <> 36
						) t
						group by catid) a join catalog_category_mapping b on a.catid = b.cat_id
                    group by b.mapping_cat) t";
        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }

    function getOrderExportReport($time){
        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $reportDate = date('Y-m-d', $time);
        $reportDate1 = date('Y-m-d', $time - 86400);
        $tb = "(select a.province, 
						orders, gmv/orders abs, gmv
						from (
						select default_name as province from directory_country_region
						where  country_id = 'VN'
						) a 
						left join (
						select d.region province, count(distinct c.entity_id) orders, sum(grand_total) gmv from  sales_flat_order c 
						join sales_flat_order_address d on c.shipping_address_id = d.entity_id
						join (select parent_id, min(created_at) 'created_at' from sales_flat_order_status_history 
						where status = 'processing' group by parent_id) b on c.entity_id = b.parent_id
						where b.created_at >= '$reportDate1 17:00:00' and b.created_at <= '$reportDate 16:59:59' 
						and c.store_id not in (20,21)
						and c.status  not in  ('canceled', 'stock_transfer', 'reject', 'pending', 'delivery_failed')
						group by d.region) t2 on a.province = t2.province) t";

        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }

    function getCulmutiveOrderExportReport($time){
        $db = new project_control();
        $db->set_db($GLOBALS['my_dbase']);

        $firstDate = date('Y-m-01', $time);
        $reportDate = date('Y-m-d', $time);
        $tb = "(select a.province,
							orders, gmv/orders abs, gmv
							FROM (
							select default_name as province from directory_country_region
							where  country_id = 'VN'
							) a left join
							(select d.region province, count(distinct c.entity_id) orders, sum(grand_total) gmv from  sales_flat_order c 
							join sales_flat_order_address d on c.shipping_address_id = d.entity_id
							join (select parent_id, min(created_at) 'created_at' from sales_flat_order_status_history 
						    where status = 'processing' group by parent_id) b on c.entity_id = b.parent_id
							where b.created_at > DATE_SUB('$firstDate 17:00:00', INTERVAL 1 DAY) and b.created_at <= '$reportDate 17:00:00' 
						    and c.store_id not in (20,21)
							and c.status  not in  ('canceled', 'stock_transfer', 'reject', 'pending', 'delivery_failed')
							group by d.region) t2 on a.province = t2.province
						) t";
        $swhere = "where 1=1";
        //var_dump($tb);die();
        return $db->get_allrows($tb,$swhere,array());
    }

}

