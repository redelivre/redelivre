<section id="mobilize-sticker" class="mobilize-widget clearfix" style="padding-left: {{ padding }}; padding-right: {{ padding }};">    
    <h6>{{ adesiveTitle }}</h6>
    <p class="section-description">
        {{ adesiveDescription }}
    </p>

    <div class="mobilize-sticked-avatar">
        <img class="mobilize-sticker" src="{{ adesiveURL }}" alt="" />
        <img width="80" height="80" src="{{ baseURL }}/wp-content/plugins/mobilize/assets/img/mistery_man.jpg" />
    </div>

    <form method="post" enctype="multipart/form-data" target="_blank">
			<input type="file" name="photo" class="mobilize-trigger-photo" />
			<br>
			<input class="mobilize-button" type="submit" value="Adesivar foto" />
    </form>

    <div class="mobilize-clear"></div>
</section>
