/*!
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
$(document).ready(function() {
    // Fixing jumping navbar
    // https://github.com/twbs/bootstrap/issues/14040#issuecomment-89720484
    $(window).on('load', function() {
        var oldSSB = $.fn.modal.Constructor.prototype.setScrollbar;
        $.fn.modal.Constructor.prototype.setScrollbar = function ()
        {
            oldSSB.apply(this);
            if (this.bodyIsOverflowing &&
                this.scrollbarWidth)
            {
                $(".navbar-fixed-top, .navbar-fixed-bottom").css("padding-right", this.scrollbarWidth);
            }
        }

        var oldRSB = $.fn.modal.Constructor.prototype.resetScrollbar;
        $.fn.modal.Constructor.prototype.resetScrollbar = function ()
        {
            oldRSB.apply(this);
            $(".navbar-fixed-top, .navbar-fixed-bottom").css("padding-right", "");
        }
    });
});
