<section id="mobilize-banners" class="mobilize-widget clearfix" style="padding-left: {{ padding }}; padding-right: {{ padding }};">
    <h6>Banners</h6>
    <p class="section-description">
        {{ bannerDescription }}
    </p>

    <div class="mobilize-banners" style="padding-left: 0;">
        <!-- banner de 250x250 -->
        <div class="image-banner">
            <img class="image-banner-1" src="{{ bannerURL250 }}">
        </div>
        
        <textarea class="code">{{ bannerCode250 }}</textarea>
        <input class="code" type="text" readonly="readonly" value="{{ bannerPermaLink }}">               
    </div>

    <div class="mobilize-banners">
        <!-- banner de 200x200 -->
        <div class="image-banner">
            <img class="image-banner-2" src="{{ bannerURL200 }}">
        </div>

        <textarea class="code">{{ bannerCode200 }}</textarea>
        <input class="code" type="text" readonly="readonly" value="{{ bannerPermaLink }}">
    </div>

    <div class="mobilize-banners">
        <!-- banner de 125x125 -->
        <div class="image-banner">
            <img class="image-banner-3" src="{{ bannerURL125 }}">
        </div>

        <textarea class="code">{{ bannerCode125 }}</textarea>
        <input class="code" type="text" readonly="readonly" value="{{ bannerPermaLink }}">
    </div>

    <div class="clear"></div>
</section>