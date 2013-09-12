<section id="mobilize-sendto" class="mobilize-widget clearfix" style="padding-left: {{ padding }}; padding-right: {{ padding }};">
    <a name="send-to"></a>
    <h6>Envie para um amigo!</h6>
    <p class="section-description">
        
        {{ enviarDescription }}

    </p>
    <!-- #standard-message -->
    <div id="mobilize-sendto-form">

        {{ enviarMessage }}

        <form method="post" action="#send-to">

            <input id="sender-name" type="text" name="sender-name" value="{{ enviarCampoNome }}" placeholder="Seu Nome">
            <input id="sender-email" type="text" value="{{ enviarCampoEmail }}" name="sender-email" placeholder="Seu E-Mail">

            <input id="recipient-email" type="text" value="{{ enviarCampoDestinos }}" name="recipient-email" placeholder="Adicione até 10 endereços de email separados por vírgula">
            <textarea id="sender-message" name="sender-message" placeholder="Adicione sua própria mensagem ou deixe em branco">{{ enviarCampoMensagem }}</textarea>
            
            <div id="standard-message">
                <div class="message-container">{{ enviarEmailCorpo }}</div>
            </div>

            <input id="submit" class="mobilize-enviar-button" type="submit" value="Enviar" name="submit">
        </form>
</section>