/***************************************************************************************************************************/
/* Toolkit */

// Remove accent from string
function remove_accents(str) {
    return str
         .replace(/[áàãâä]/gi, "a")
         .replace(/[éè¨ê]/gi, "e")
         .replace(/[íìïî]/gi, "i")
         .replace(/[óòöôõ]/gi, "o")
         .replace(/[úùüû]/gi, "u")
         .replace(/[ç]/gi, "c")
         .replace(/[ñ]/gi, "n");
}