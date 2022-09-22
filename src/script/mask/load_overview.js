import {set_status} from "../common/status_writer.js";

$(document).on('DOMContentLoaded', async() => {
    console.log('Loading overview page');
    await load_overview_mask()
        .catch(error => {
            set_status('#mask-overview-status', 'Erreur lors du chargement ', 'error');
        }).finally(() => {
            console.log('Loading overview table done');
        });
});

async function load_overview_mask()
{
    set_status('#mask-overview-status', 'Chargement...', 'secondary');
    const mask_overview_load_str = await get_mask_overview_load();

    console.log(mask_overview_load_str);
    set_status('#mask-overview-status', 'Chargement terminé', 'primary');
}

async function get_mask_overview_load()
{
    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'src/script/ajax_requests/mask/get_mask_overview_load.php',
            type: 'POST',
            success: (data) => {
                $('#mask-overview-table').html(data);
                resolve(true);
            },
            error: (xhr, status, error) => {
                $('#mask-overview-table').html('Erreur : ' + error);
                reject(error);
            }
        });
    });
}