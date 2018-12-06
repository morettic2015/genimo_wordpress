
<script>
    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id))
            return;
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v3.0&appId=1617463248479622&autoLogAppEvents=1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>
<h3>Deixe seus comentários, dúvidas ou sugestões abaixo.</h3>
<div id="fb-root" style="width: 100% !important;"></div>
<div class="fb-comments" data-href="<?php echo get_permalink(get_the_ID()); ?>" data-width="100%" data-numposts="25" style="width: 100% !important;"></div>
