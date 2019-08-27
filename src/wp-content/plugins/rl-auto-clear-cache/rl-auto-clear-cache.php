<?php

/*
 Plugin Name: RedeLivre Auto Clear Cache
 Plugin URI: https://github.com/redelivre/rl-auto-clear-cache
 Description: Clear Cache from W3 and CloudFlare
 Version: 0.0.1-beta.1
 Author: RedeLivre
 Author URI: http://redelivre.org.br
 
 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

function post_status($new_status, $old_status, $post) {
	global $custom_post_types;
	$custom_post_types = array (
			"news" => "news", // Theme post type news
			"pauta" => "pautas" // Plugin Delibera post type
	);

	if (array_key_exists ( $post->post_type, $custom_post_types ) && ($new_status === "publish" || $old_status === "publish")) {
		if (defined ( 'W3TC' )) {
			// Flush everything!
			w3tc_flush_all ();
		}
		if (defined ( 'CLOUDFLARE_PLUGIN_DIR' )) {
			$CF_Hooks = new CF\WordPress\Hooks();
			$CF_Hooks->purgeCacheEverything();
		}
	}
}
add_action ( 'transition_post_status', 'post_status', 10, 3 );


