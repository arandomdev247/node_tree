/**
 * Fichier de chargement du résumé des masques
 */

import {set_status} from "../common/status_writer.js";
import {get_id_from_filename} from "../common/id_getter.js";

$(document).on('DOMContentLoaded', async() => {
   console.log('Loading summary page');
   const mask_id = get_id_from_filename();
   console.log("id = " + mask_id);
   await load_data_mask(mask_id);

});

async function load_data_mask(mask_id)
{
   set_status("#mask-status", "Chargement...", "secondary");
   let data = await get_mask_summary_load(mask_id);
   $("#mask-summary").html(data);
   set_status("#mask-status", "Chargement terminé !", "primary");
}

async function get_mask_summary_load(mask_id)
{
   return new Promise((resolve, reject) => {
      $.ajax({
         type: 'POST',
         url: 'src/script/ajax_requests/mask/get_mask_summary_load.php',
         data: {'id': mask_id},
         cache: false,
         success: (data) => {
            resolve(data);
         }
      }).catch((err) => {
         console.log(err);
         reject(err);
      }).done(() => {
         console.log('Summary loaded');
      });
   });
}