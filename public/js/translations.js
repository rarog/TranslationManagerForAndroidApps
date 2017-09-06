/*!
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
function hideSelectionHint(hide) {
    var node = $("#selectionHint");
    if (hide) {
        node.addClass("hidden");
    } else {
        node.removeClass("hidden");
    }
}

function hideSpinner(hide) {
    var node = $("#spinner");
    if (hide) {
        node.addClass("hidden");
    } else {
        node.removeClass("hidden");
    }
}

function hideTranslationRow(hide) {
    var node = $("#translationRow");
    if (hide) {
        node.addClass("hidden");
        $("translationTableBody").empty();
    } else {
        node.removeClass("hidden");
        var table = $('#translations').DataTable();
    }
}

function setSelectionNeededState() {
    hideSelectionHint(false);
    hideSpinner(true);
    hideTranslationRow(true);
}

function showModalError() {
	$('#modalError').modal();
}

var curResources = resources;

$("#showAll").on("change", function (event) {
	var appSelect = $("#app");
	appSelect.selectpicker('deselectAll');
    $("option", appSelect).remove();

    curApps = (event.target.checked) ? appsAll : apps;
    curResources = (event.target.checked) ? resourcesAll : resources;

    $.each(curApps, function(index, value) {
    	appSelect.append('<option value="' + index + '">' + value + '</option>');
    });
});

$("#app").on("changed.bs.select", function(event, clickedIndex, newValue, oldValue) {
    setSelectionNeededState();

    var resourceSelect = $("#resource");
    $("option", resourceSelect).remove();
    if (newValue) {
        var key = $(this).val();
        if (key in curResources) {
            $.each(curResources[key], function(index, value) {
                resourceSelect.append('<option value="' + index + '">' + value + '</option>');
            });
        }
    }
    resourceSelect.selectpicker("refresh");
});

$("#resource").on("changed.bs.select", function(event, clickedIndex, newValue, oldValue) {
    if (newValue) {
        hideSelectionHint(true);
        hideSpinner(false);
        hideTranslationRow(true);

        var app = $("#app").val();
        var resource = $(this).val();

        $.ajax({
            url: translationsPath + "/app/" + app + "/resource/" + resource,
            dataType: "json",
            method: "GET"
        })
        .done(function(data) {
            var table = $('#translations').DataTable();

            table.clear()
                .rows.add(data)
                .draw();
            hideSelectionHint(true);
            hideSpinner(true);
            hideTranslationRow(false);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            setSelectionNeededState();
        });
    } else {
        setSelectionNeededState();
    }
});

$("#translations").on("click", ".translationDetails", function(event) {
    var app = $("#app").val();
    var resource = $("#resource").val();
    var entry = $(event.target).data("entryid");

    $.ajax({
        url: detailsPath + "/app/" + app + "/resource/" + resource + "/entry/" + entry,
        dataType: "json",
        method: "GET"
    })
    .done(function(data) {
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
    	showModalError();
    });
});

// Enable Bootstrap Tooltips
$(function() {
  $('[data-toggle="tooltip"]').tooltip()
});
