$j(document).ready(function () {
    $j("input[name=is_vat]").change(function () {
        if ($j(this).is(':checked')) {
            $j("[name*=vat][name!=is_vat]").closest('.form-group.row').show();
            $j("input[name=vat_name]").val($j('input[name=full_name').val());
            $j("textarea[name*=vat]").each(function (e) {
                if (!$j(this).val())
                    $j(this).val(
                        $j('textarea[name=street]').val() + "\n"
                        + $j('select[name=city_id] option:selected').text() + "\n"
                        + $j('select[name=region_id] option:selected').text() + "\n"
                    );
            });
        } else {
            $j("[name*=vat][name!=is_vat]").closest('.form-group.row').hide();
        }
    }).change();
});