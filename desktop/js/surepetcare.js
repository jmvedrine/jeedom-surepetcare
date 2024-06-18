
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

$('#bt_syncEqLogic').on('click', function () {
  sync();
});

document.querySelector('.eqLogicAction[data-action=createCommunityPost]')?.addEventListener('click', function(event) {
	jeedom.plugin.createCommunityPost({
		type: eqType,
		error: function(error) {
			domUtils.hideLoading()
			jeedomUtils.showAlert({
			  message: error.message,
			  level: 'danger'
			})
		},
		success: function(data) {
			let element = document.createElement('a');
			element.setAttribute('href', data.url);
			element.setAttribute('target', '_blank');
			element.style.display = 'none';
			document.body.appendChild(element);
			element.click();
			document.body.removeChild(element);
		}
	});
	return;
});

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

function printEqLogicHelper(label,name,_eqLogic){
	var trm = '<tr><td class="col-sm-2"><span style="font-size : 1em;">'+label+'</span></td><td><span class="label label-default" style="font-size : 1em;"> <span class="eqLogicAttr" data-l1key="configuration" data-l2key="'+name+'"></span></span></td></tr>';
	
	$('#table_infoseqlogic tbody').append(trm);
	$('#table_infoseqlogic tbody tr:last').setValues(_eqLogic, '.eqLogicAttr');
}

// fonction executée par jeedom lors de l'affichage des details d'un eqlogic
function printEqLogic(_eqLogic) {
	if (!isset(_eqLogic)) {
		var _eqLogic = {configuration: {}};
	}
	if (!isset(_eqLogic.configuration)) {
		_eqLogic.configuration = {};
	}
	$('#table_infoseqlogic tbody').empty();
    printEqLogicHelper("{{Foyer}}","household_name",_eqLogic);
    if (_eqLogic.configuration.type=="device") {
        printEqLogicHelper("{{Modèle}}","product_name",_eqLogic);
        printEqLogicHelper("{{Numéro de série}}","serial_number",_eqLogic);
        printEqLogicHelper("{{Adresse MAC}}","mac_address",_eqLogic);
        printEqLogicHelper("{{Version}}","version",_eqLogic);
        printEqLogicHelper("{{Créé le}}","created_at",_eqLogic);
        printEqLogicHelper("{{Mis à jour le}}","updated_at",_eqLogic);
        $('#img_device').attr("src", 'plugins/surepetcare/core/config/images/device'+_eqLogic.configuration.product_id+'.png');
    }
    if (_eqLogic.configuration.type=="pet") {
        printEqLogicHelper("{{Poids (kg)}}","weight",_eqLogic);
        printEqLogicHelper("{{Commentaire}}","comments",_eqLogic);
        printEqLogicHelper("{{Tag id}}","tag_id",_eqLogic);
        printEqLogicHelper("{{Race}}","breed_name",_eqLogic);
        printEqLogicHelper("{{Sexe}}","gender",_eqLogic);
        printEqLogicHelper("{{Version}}","version",_eqLogic);
        printEqLogicHelper("{{Créé le}}","created_at",_eqLogic);
        printEqLogicHelper("{{Mis à jour le}}","updated_at",_eqLogic);
        $('#img_device').attr("src", _eqLogic.configuration.photo_location);
        $('#curfew_lock_time').hide();
        $('#curfew_unlock_time').hide();
    }
    printScheduling(_eqLogic);
}

function printScheduling(_eqLogic){
  $.ajax({
    type: 'POST',
    url: 'plugins/surepetcare/core/ajax/surepetcare.ajax.php',
    data: {
      action: 'getLinkCalendar',
      id: _eqLogic.id,
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      $('#div_schedule').empty();
      console.log(data);
      if(data.result.length == 0){
        $('#div_schedule').append("<center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez encore aucune programmation. Veuillez cliquer <a href='index.php?v=d&m=calendar&p=calendar'>ici</a> pour programmer votre équipement à l'aide du plugin agenda}}</span></center>");
      }else{
        var html = '<legend>{{Liste des programmations du plugin Agenda liées à l\'objet Sure PetCare}}</legend>';
        for (var i in data.result) {
          var color = init(data.result[i].cmd_param.color, '#2980b9');
          if(data.result[i].cmd_param.transparent == 1){
            color = 'transparent';
          }
          html += '<span class="label label-info cursor" style="font-size:1.2em;background-color : ' + color + ';color : ' + init(data.result[i].cmd_param.text_color, 'black') + '">';
          html += '<a href="index.php?v=d&m=calendar&p=calendar&id='+data.result[i].eqLogic_id+'&event_id='+data.result[i].id+'" style="color : ' + init(data.result[i].cmd_param.text_color, 'black') + '">'
          if (data.result[i].cmd_param.eventName != '') {
            html += data.result[i].cmd_param.icon + ' ' + data.result[i].cmd_param.eventName;
          } else {
            html += data.result[i].cmd_param.icon + ' ' + data.result[i].cmd_param.name;
          }
          html += '</a></span>';
          html += ' ' + data.result[i].startDate.substr(11,5) + ' à ' + data.result[i].endDate.substr(11,5)+'<br\><br\>';
        }
        $('#div_schedule').empty().append(html);
      }
    }
  });
}
function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}}
  }
  if (!isset(_cmd.configuration)) {
    _cmd.configuration = {}
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="logicalId" value="0" style="width : 70%; display : inline-block;" placeholder="{{Commande}}"><br/>';
  tr += '</td>';
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste de valeur|texte séparé par ;}}" title="{{Liste}}">';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>';
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'; 
  tr += '</td>';
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
  }
  tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {type: 'info'},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'})
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
  }
  })
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
