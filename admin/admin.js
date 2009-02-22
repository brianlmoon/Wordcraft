/**
 * General javascript used by the admin
 *
 * @author     Brian Moon <brian@moonspot.net>
 * @copyright  1997-Present Brian Moon
 * @package    Wordcraft
 * @license    http://wordcraft.googlecode.com/files/license.txt
 * @link       http://wordcraft.googlecode.com/
 *
 */

function changePreview(template) {
    document.getElementById("preview").href = '../index.php?preview=' + escape(template);
}
