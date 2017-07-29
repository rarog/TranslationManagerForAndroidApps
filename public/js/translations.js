/*!
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
$("#app").on("changed.bs.select", function(event, clickedIndex, newValue, oldValue) {
    var resourceSelect = $("#resource");
    $("option", resourceSelect).remove();
    if (newValue) {
    	var key = $(this).val();
    	if (key in resources) {
    		$.each(resources[key], function(index, value) {
    			// TODO: add options to resourceSelect
    		});
    	}
    }
    resourceSelect.selectpicker("refresh");
});
