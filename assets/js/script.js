(function ($) {

    var timeout;

    $('.dropdown').on({
        mouseenter: function() {
            $('#cart-dropdown').show();
        },
        mouseleave: function() {
            timeout = setTimeout(function() {
                $('#cart-dropdown').hide();
            }, 400);
        }
    });

// laisse le contenu ouvert à son survol
// le cache quand la souris sort
    $('#cart-dropdown').on({
        mouseenter: function() {
            clearTimeout(timeout);
        },
        mouseleave: function() {
            $('#cart-dropdown').hide();
        }
    });

    $(".button").mouseenter(function () {
        $(".button").addClass("linked");
    });

    $(".button").mouseleave(function () {
        $(".button").removeClass("linked");
    });

})(jQuery);


function b64EncodeUnicode(str) {
    return btoa( unescape( encodeURIComponent( str ) ) );
}

function b64DecodeUnicode(str) {
    return decodeURIComponent( escape( atob( str ) ) );
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();

    // règle le pb des caractères interdits
    if ('btoa' in window) {
        cvalue = b64EncodeUnicode(cvalue);

    }
    document.cookie = cname + "=" + cvalue + "; " + expires+';path=/';

}

function saveCart(inCartItemsNum, cartArticles) {
    setCookie('inCartItemsNum', inCartItemsNum, 5);
    setCookie('cartArticles', JSON.stringify(cartArticles), 5);
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');

    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c[0] == ' ') {
            c = c.substring(1);
        }

        if (c.indexOf(name) != -1) {
            if ('btoa' in window) {
                return decodeURIComponent( escape( atob(c.substring(name.length,c.length))));
            }
            else {
                return c.substring(name.length,c.length);
            }
        }
    }
    return false;
}

// variables pour stocker le nombre d'articles et leurs noms
var inCartItemsNum;
var cartArticles;

// affiche/cache les éléments du panier selon s'il contient des produits
function cartEmptyToggle() {
    if (inCartItemsNum > 0) {
        $('#cart-dropdown .hidden').removeClass('hidden');
        $('#empty-cart-msg').hide();
    }

    else {
        $('#cart-dropdown .go-to-cart').addClass('hidden');
        $('#empty-cart-msg').show();
    }
}

// récupère les informations stockées dans les cookies
inCartItemsNum = parseInt(getCookie('inCartItemsNum') ? getCookie('inCartItemsNum') : 0);
cartArticles = getCookie('cartArticles') ? JSON.parse(getCookie('cartArticles')) : [];


cartEmptyToggle();

// affiche le nombre d'article du panier dans le widget
$('#in-cart-items-num').html(inCartItemsNum);

// hydrate le panier
var items = '';
cartArticles.forEach(function(v) {
    items += '<li id="'+ v.id +'">'+ v.name +'<br><small>Quantité : <span class="qt">'+ v.qt +'</span></small></li>';
});

$('#cart-dropdown').prepend(items);

// click bouton ajout panier
$('.add-to-cart').click(function() {

    // récupération des infos du produit
    var $this = $(this);
    var id = $this.attr('data-id');
    var name = $this.attr('data-name');
    var onlyName = name.split(' - ');
    var price = $this.attr('data-price');
    //var weight = $this.attr('data-weight');
    var qt = parseInt($('#qt').val());
    inCartItemsNum += qt;

    // mise à jour du nombre de produit dans le widget
    $('#in-cart-items-num').html(inCartItemsNum);

    var newArticle = true;

    // vérifie si l'article est pas déjà dans le panier
    cartArticles.forEach(function(v) {
        // si l'article est déjà présent, on incrémente la quantité
        if (v.id == id) {
            newArticle = false;
            v.qt += qt;
            $('#'+ id).html('' + onlyName[0] +'<br><small>Quantité : <span class="qt">'+ v.qt +'</span></small>');
        }
    });

    // s'il est nouveau, on l'ajoute
    if (newArticle) {
        $('#cart-dropdown').prepend('<li id="'+ id +'">'+ onlyName[0] +'<br><small>Quantité : <span class="qt">'+ qt +'</span></small></li>');

        cartArticles.push({
            id: id,
            name: name,
            price: price,
            qt: qt
        });
    }

    // sauvegarde le panier
    saveCart(inCartItemsNum, cartArticles);

    // affiche le contenu du panier si c'est le premier article
    cartEmptyToggle();
});

// si on est sur la page ayant pour url monsite.fr/command.phtml
if (window.location.pathname == '/coque/command.phtml') {
    var items = '';
    var subTotal = 0;
    var total;

    /* on parcourt notre array et on créé les lignes du tableau pour nos articles :
    * - Le nom de l'article
    * - son prix
    * - la dernière colonne permet de modifier la quantité et de supprimer l'article
    *
    * On met aussi à jour le sous total et le poids total de la commande
    */


    cartArticles.forEach(function(v) {
        // opération sur un entier pour éviter les problèmes d'arrondis
        var itemPrice = v.price.replace(',', '.') * 1000;
        items += '<tr data-id="'+ v.id +'">\
             <td>'+ v.name +'</td>\
             <td>'+ v.price +'€</td>\
             <td><span class="qt">'+ v.qt +'</span> <span class="qt-minus">–</span> <span class="qt-plus">+</span> \
             <a class="delete-item">Supprimer</a></td></tr>';
        subTotal += v.price.replace(',', '.') * v.qt;

    });


    // On insère le contenu du tableau et le sous total
    $('#cart-tablebody').empty().html(items);
    $('.subtotal').html(subTotal.toFixed(2).replace('.', ','));

    // lorsqu'on clique sur le "+" du panier
    $('.qt-plus').on('click', function() {
        var $this = $(this);

        // récupère la quantité actuelle et l'id de l'article
        var qt = parseInt($this.prevAll('.qt').html());
        var id = $this.parent().parent().attr('data-id');
        //var artWeight = parseInt($this.parent().parent().attr('data-weight'));

        // met à jour la quantité et le poids
        inCartItemsNum += 1;
        //weight += artWeight;
        $this.prevAll('.qt').html(qt + 1);
        $('#in-cart-items-num').html(inCartItemsNum);
        $('#'+ id + ' .qt').html(qt + 1);

        // met à jour cartArticles
        cartArticles.forEach(function(v) {
            // on incrémente la qt
            if (v.id == id){
                v.qt += 1;

                // récupération du prix
                // on effectue tous les calculs sur des entiers
                subTotal = ((subTotal * 1000) + (parseFloat(v.price.replace(',', '.')) * 1000)) / 1000;
            }
        });

        // met à jour la quantité du widget et sauvegarde le panier
        $('.subtotal').html(subTotal.toFixed(2).replace('.', ','));
        saveCart(inCartItemsNum, cartArticles);
    });

    // quantité -
    $('.qt-minus').click(function() {
        var $this = $(this);
        var qt = parseInt($this.prevAll('.qt').html());
        var id = $this.parent().parent().attr('data-id');
        //var artWeight = parseInt($this.parent().parent().attr('data-weight'));

        if (qt > 1) {
            // maj qt
            inCartItemsNum -= 1;
            //weight -= artWeight;
            $this.prevAll('.qt').html(qt - 1);
            $('#in-cart-items-num').html(inCartItemsNum);
            $('#'+ id + ' .qt').html(qt - 1);

            cartArticles.forEach(function(v) {
                // on décrémente la qt
                if (v.id == id) {
                    v.qt -= 1;

                    // récupération du prix
                    // on effectue tous les calculs sur des entiers
                    subTotal = ((subTotal * 1000) - (parseFloat(v.price.replace(',', '.')) * 1000)) / 1000;
                }
            });

            $('.subtotal').html(subTotal.toFixed(2).replace('.', ','));
            saveCart(inCartItemsNum, cartArticles);
        }
    });

    // suppression d'un article
    $('.delete-item').click(function() {
        var $this = $(this);
        var qt = parseInt($this.prevAll('.qt').html());
        var id = $this.parent().parent().attr('data-id');
       // var artWeight = parseInt($this.parent().parent().attr('data-weight'));
        var arrayId = 0;
        var price;

        // maj qt
        inCartItemsNum -= qt;
        $('#in-cart-items-num').html(inCartItemsNum);

        // supprime l'item du DOM
        $this.parent().parent().hide(600);
        $('#'+ id).remove();

        cartArticles.forEach(function(v) {
            // on récupère l'id de l'article dans l'array
            if (v.id == id) {
                // on met à jour le sous total et retire l'article de l'array
                // as usual, calcul sur des entiers
                var itemPrice = v.price.replace(',', '.') * 1000;
                subTotal -= (itemPrice * qt) / 1000;
                //weight -= artWeight * qt;
                cartArticles.splice(arrayId, 1);

                return false;
            }
            arrayId++;
        });

        $('.subtotal').html(subTotal.toFixed(2).replace('.', ','));
        saveCart(inCartItemsNum, cartArticles);
        cartEmptyToggle();
    });
}


