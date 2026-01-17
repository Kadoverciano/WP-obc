const accordionItems = document.querySelectorAll('.accordion-item');

accordionItems.forEach((item) => {
  const title = item.querySelector('.accordion-title');

  title.addEventListener('click', (e) => {
    e.preventDefault();

    // Закрываем все, кроме текущего
    accordionItems.forEach((otherItem) => {
      if (otherItem !== item) {
        otherItem.classList.remove('active');
      }
    });

    // Переключаем текущий
    item.classList.toggle('active');
  });
});

// function loadToCoins() {
//     $.ajax({
//         url: '<?php echo admin_url("admin-ajax.php"); ?>',
//         type: 'POST',
//         data: { action: 'get_to_coins', from_coin: selectedFrom },
//         success: function(response){
//             $('#coins-to-list').html(response);

//             // добавляем плавный эффект появления
//             $('#coins-to-list .coin-item-right').each(function(i){
//                 let el = $(this);
//                 setTimeout(function(){ el.addClass('show'); }, i*50);
//             });

//             // автоматически выбрать первую
//             $('#coins-to-list .coin-item-right').first().addClass('active');
//             updateReceive();
//         }
//     });
// }
