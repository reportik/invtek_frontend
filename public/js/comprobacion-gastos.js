var InicializaFunciones = (function () {
  'use strict';
  return {
    init: function () {
      InicializaComponentes();
    }
  };
})();

$(function () {
  InicializaFunciones.init();
  //$('.selectpicker').selectpicker();
  //console.log('helloo');
});

var InicializaComponentes = function () {
  $('#ceco').selectpicker('refresh');
};
