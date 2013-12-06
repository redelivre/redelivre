<section id="mobilize-banners" class="mobilize-widget clearfix" style="padding-left: {{ padding }}; padding-right: {{ padding }};">
    <h6>{{ bannerTitle }}</h6>
    <p class="section-description">
        {{ bannerDescription }}
    </p>

    <div class="mobilize-banners" style="padding-left: 0;">
        <!-- banner de 250x250 -->
        <div class="mobilize-image-banner">
            <img class="mobilize-image-banner-1" src="{{ bannerURL250 }}">
        </div>
        
        <textarea class="mobilize-code">{{ bannerCode250 }}</textarea>
        <input class="mobilize-code" type="text" readonly="readonly" value="{{ bannerPermaLink }}">               
    </div>

    <div class="mobilize-banners">
        <!-- banner de 200x200 -->
        <div class="mobilize-image-banner">
            <img class="mobilize-image-banner-2" src="{{ bannerURL200 }}">
        </div>

        <textarea class="mobilize-code">{{ bannerCode200 }}</textarea>
        <input class="mobilize-code" type="text" readonly="readonly" value="{{ bannerPermaLink }}">
    </div>

    <div class="mobilize-banners">
        <!-- banner de 125x125 -->
        <div class="mobilize-image-banner">
            <img class="mobilize-image-banner-3" src="{{ bannerURL125 }}">
        </div>

        <textarea class="mobilize-code">{{ bannerCode125 }}</textarea>
        <input class="mobilize-code" type="text" readonly="readonly" value="{{ bannerPermaLink }}">
    </div>

    <div class="mobilize-clear"></div>
</section>
