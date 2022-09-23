<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-whitepay" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
        <h1><?php echo $heading_title; ?></h1>
        <ul class="breadcrumb">
          <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
          <?php } ?>
        </ul>
      </div>
    </div>
    <div class="container-fluid">
      <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-whitepay" class="form-horizontal">
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-merchant"><?php echo $entry_merchant; ?></label>
            <div class="col-sm-10">
              <input type="text" name="whitepay_merchant" value="<?php echo $whitepay_merchant; ?>" placeholder="<?php echo $entry_merchant; ?>" id="input-merchant" class="form-control" />
              <?php if ($error_merchant) { ?>
                <div class="text-danger"><?php echo $error_merchant; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-whitepay_api_key">API KEY</label>
            <div class="col-sm-10">
              <input type="text" name="whitepay_api_key" value="<?php echo $whitepay_api_key; ?>" placeholder="<?php echo $whitepay_api_key; ?>" id="input-whitepay_api_key" class="form-control" />
              <?php if ($error_signature) { ?>
                <div class="text-danger"><?php echo $error_signature; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-whitepay_webhook_key">WEBHOOK KEY</label>
            <div class="col-sm-10">
              <input type="text" name="whitepay_webhook_key" value="<?php echo $whitepay_webhook_key; ?>" placeholder="<?php echo $whitepay_webhook_key; ?>" id="input-whitepay_webhook_key" class="form-control" />
              <?php if ($error_signature) { ?>
                <div class="text-danger"><?php echo $error_signature; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-type"><?php echo $entry_type; ?></label>
            <div class="col-sm-10">
              <select name="whitepay_type" id="input-type" class="form-control">
                <?php if ($whitepay_type == 'whitepay') { ?>
                  <option value="whitepay" selected="selected"><?php echo $text_pay; ?></option>
                <?php } else { ?>
                  <option value="whitepay"><?php echo $text_pay; ?></option>
                <?php } ?>
                <?php if ($whitepay_type == 'card') { ?>
                  <option value="card" selected="selected"><?php echo $text_card; ?></option>
                <?php } else { ?>
                  <option value="card"><?php echo $text_card; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="whitepay_total" value="<?php echo $whitepay_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" title="">Исключить ценовые группы</span></label>
            <div class="col-sm-10">
              <div class="row">
                <?php foreach ($pricegroups as $pricegroup) { ?>
                  <div class="col-sm-2">
                    <div class="checkbox">
                      <label>
                        <?php if (in_array($pricegroup['pricegroup_id'], $whitepay_exclude_pricegroups)) { ?>
                          <input type="checkbox" name="whitepay_exclude_pricegroups[]" value="<?php echo $pricegroup['pricegroup_id']; ?>" checked="checked" />
                          <?php echo $pricegroup['name']; ?>
                        <?php } else { ?>
                          <input type="checkbox" name="whitepay_exclude_pricegroups[]" value="<?php echo $pricegroup['pricegroup_id']; ?>" />
                          <?php echo $pricegroup['name']; ?>
                        <?php } ?>
                      </label>
                    </div>
                  </div>
                <?php } ?>
              </div>
            </div>
          </div>

          <div class="form-group">
              <label class="col-sm-2 control-label" for="input-order-status">Статус успешной оплаты</label>
              <div class="col-sm-10">
                <select name="whitepay_success_order_status_id" id="input-order-status" class="form-control">
                  <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status['order_status_id'] == $whitepay_success_order_status_id) { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                    <?php } else { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label" for="input-order-status">Статус нового заказа</label>
              <div class="col-sm-10">
                <select name="whitepay_order_status_id" id="input-order-status" class="form-control">
                  <?php foreach ($order_statuses as $order_status) { ?>
                    <?php if ($order_status['order_status_id'] == $whitepay_order_status_id) { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                    <?php } else { ?>
                      <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
              <div class="col-sm-10">
                <select name="whitepay_geo_zone_id" id="input-geo-zone" class="form-control">
                  <option value="0"><?php echo $text_all_zones; ?></option>
                  <?php foreach ($geo_zones as $geo_zone) { ?>
                    <?php if ($geo_zone['geo_zone_id'] == $whitepay_geo_zone_id) { ?>
                      <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                    <?php } else { ?>
                      <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                    <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
              <div class="col-sm-10">
                <select name="whitepay_status" id="input-status" class="form-control">
                  <?php if ($whitepay_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
              <div class="col-sm-10">
                <input type="text" name="whitepay_sort_order" value="<?php echo $whitepay_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php echo $footer; ?>