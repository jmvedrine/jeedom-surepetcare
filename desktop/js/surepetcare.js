
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

$('#bt_showSurepetcareModuleConfiguration').on('click', function () {
  $('#md_modal').dialog({title: "{{Configuration du module}}"});
  $('#md_modal').load('index.php?v=d&plugin=surepetcare&modal=node&id='+$('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
});

$('#bt_syncEqLogic').on('click', function () {
  sync();
});

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

function getProductList(_id) {
  $.ajax({
    type: "POST",
    url: "plugins/surepetcare/core/ajax/surepetcare.ajax.php",
    data: {
      action: "getProductList",
      id: _id,
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      var options = '';
      for (var i in data.result) {
        if (data.result[i]['selected'] == 1){
          options += '<option value="'+data.result[i]['file']+'" selected>'+i+'</option>';
        } else {
          options += '<option value="'+data.result[i]['file']+'">'+i+'</option>';
        }
      }
      if (options == ''){
        options += '<option value="">Aucun</option>';
      }
      $(".modelList").show();
      $(".listModel").html(options);
      icon = $('.eqLogicAttr[data-l1key=configuration][data-l2key=iconProduct]').value();
      if(icon != '' && icon != null){
        $('#img_device').attr("src", 'plugins/surepetcare/core/config/images/'+icon);
      } else {
        $('#img_device').attr("src", 'plugins/surepetcare/plugin_info/surepetcare_icon.png');
      }
    }
  });
}

function sync(){
  $('#div_alert').showAlert({message: '{{Synchronisation en cours}}', level: 'warning'});
  $.ajax({
    type: "POST",
    url: "plugins/surepetcare/core/ajax/surepetcare.ajax.php",
    data: {
      action: "sync",
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_alert').showAlert({message: '{{Operation realisee avec succes}}', level: 'success'});
      window.location.reload();
    }
  });
}
