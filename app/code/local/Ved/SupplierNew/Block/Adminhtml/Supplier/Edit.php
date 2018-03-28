<?php

class Ved_SupplierNew_Block_Adminhtml_Supplier_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
  public function __construct()
  {
    parent::__construct();

//    $this->setDestElementId("edit_form");
//    $this->setTitle(Mage::helper("supplier")->__("Supplier Information"));

    $this->_objectId = "supplier_id";
    $this->_blockGroup = "supplier";
    $this->_controller = "adminhtml_supplier";
    $this->_updateButton("save", "label", Mage::helper("supplier")->__("Save"));
    $this->_updateButton("delete", "label", Mage::helper("supplier")->__("Delete"));
    $this->removeButton("delete");

    $this->_addButton("saveandcontinue", array(
      "label" => Mage::helper("supplier")->__("Save And Continue Edit"),
      "onclick" => "saveAndContinueEdit()",
      "class" => "save",
    ), -100);

    $this->_formScripts[] = "
      function saveAndContinueEdit(){
        editForm.submit($('edit_form').action+'back/edit/');
      }

      //<![CDATA[
      var districtModel = Class.create();
      districtModel.prototype = {
        initialize: function() {
          this.loader = new varienLoader(true);
          this.citiesUrl = '".$this->getUrl('*/*/provinceCity')."';
        },

        reloadCityField: function(provinceElement) {
          if (provinceElement.id) {
            var districtElement = $(provinceElement.id.replace(/province/, 'district'));
            if (districtElement) {
              this.districtElement = districtElement;
              this.provinceEl = provinceElement;
              if (provinceElement.value) {
                var url = this.citiesUrl + 'parent/' + provinceElement.value;
                this.loader.load(url, {}, this.refreshCityField.bind(this));
              } else {
                // Set empty text field in district
                this.refreshCityField('[]');
              }
            }
          }
        },

        // serverResponse is either string with server response, or object to force some paricular data setting
        refreshCityField: function(serverResponse) {
          if (!serverResponse) return;

          var data = eval('(' + serverResponse + ')');

          var row = Element.previous(this.districtElement.parentNode, 0);

          // Set cities and refresh controls
          // We use a pair of 'district' and 'district_id' to properly submit data:
          // manually entered text goes in 'district' and selected option id goes in 'district_id'
          var districtHtmlName = this.districtElement.name;
          var districtHtmlId = this.districtElement.id;

          var districtElementSelect = $(districtHtmlId+'_select');
          var districtSelectValue = '';
          if(districtElementSelect) {
            districtSelectValue = districtElementSelect.value;
          }

          var html = '';
          if (data.length) {
            // Create visible selectbox 'district_id' and hidden 'district'
            html = '<select name=\"' + districtHtmlName + '\" id=\"' + districtHtmlId + '\" class=\"required-entry select\" title=\"' + this.districtElement.title + '\">';
            for (i = 0; i < data.length; i++) {
              if (data[i]) {
                html += '<option value=\"' + data[i] + '\"';
                if (districtSelectValue && districtSelectValue == data[i]) {
                  html += ' selected=\"selected\"';
                }
                html += '>' + data[i] + '</option>';
              }
            }
            html += '</select>';
          } else {
            // Create visible text input 'district' and hidden 'district_id'
            html = '<input type=\"text\" name=\"' + districtHtmlName + '\" id=\"' + districtHtmlId + '\" class=\"input-text required-entry\" title=\"' + this.districtElement.title + '\" />';
          }

          var parentNode = this.districtElement.parentNode;
          parentNode.innerHTML = html;
          this.districtElement = $(districtHtmlId);
        }
      }

      supplierDistrict = new districtModel();
      //]]>
    ";
  }

  public function getHeaderText()
  {
    if (Mage::registry("supplier_data") && Mage::registry("supplier_data")->getId()) {
      return Mage::helper("supplier")->__("Edit Supplier '%s'", $this->htmlEscape(Mage::registry("supplier_data")->getSupplierName()));
    } else {
      return Mage::helper("supplier")->__("Add Supplier");
    }
  }
}