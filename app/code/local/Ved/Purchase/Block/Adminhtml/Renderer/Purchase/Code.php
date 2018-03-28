<?php
/**
 * Class Ved_Purchase_Block_Adminhtml_Renderer_Purchase_Code
 */
class Ved_Purchase_Block_Adminhtml_Renderer_Purchase_Code extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        /**
         * @var Mage_Core_Model_Date $date
         */
        $data_render = $row->getData($this->getColumn()->getIndex());
        if ($this->getColumn()->getIndex() == 'created_at') {
            $data_render = DateTime::createFromFormat('Y-m-d H:i:s',
                $row->getCreatedAt(),
                new DateTimeZone('UTC')
            );
            $data_render->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
            $data_render = $data_render->format('d-m-Y H:i:s');
        }
        if ($this->getColumn()->getIndex() == 'receive_date') {
            if ($row->getReceiveDate() != null) {
                $data_render = DateTime::createFromFormat('Y-m-d H:i:s',
                    $row->getReceiveDate(),
                    new DateTimeZone('UTC')
                );
                $data_render->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                $data_render = $data_render->format('d-m-Y H:i:s');
            }
            else {
                $data_render = '';
            }
        }
        if ($row->getReceiveDate() == null) {
            $re = DateTime::createFromFormat('Y-m-d H:i:s',
                date('Y-m-d H:i:s', strtotime('+3 hours', time())),
                new DateTimeZone('UTC')
            )->getTimestamp();
        }
        else {
            $re = DateTime::createFromFormat('Y-m-d H:i:s',
                $row->getReceiveDate(),
                new DateTimeZone('UTC')
            )->getTimestamp();
        }
        $now = DateTime::createFromFormat('Y-m-d H:i:s',
            now(),
            new DateTimeZone('UTC')
        )->getTimestamp();
        if ($re < $now && $row->getStatus() == 1) {
            $html .= "<span style='color: #f44141; font-weight: bold;'>".$data_render."</span>";
        }
        else if ($re - $now <= 1800 && $row->getStatus() == 1) {
            $html .= "<span style='color: #d9b611; font-weight: bold;'>".$data_render."</span>";
        }
        else {
            $html .= "<span>".$data_render."</span>";
        }
        return $html;
    }
}