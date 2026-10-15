<?php
/**
 * Blog — seções de categoria (Figma 8314:1689 · mobile 8673:3920), uma por categoria com posts.
 * Template: template-parts/blog/03-categorias.php (loop) · espelha partials/blog-03-categoria.html.
 *
 * Categoria nativa  nome → título (8314:1706) · slug → âncora das abas do hero (#slug)
 *                   term meta _art_ordem → ordem da seção na página (campo na tela de categorias)
 * Post nativo       título (8314:1713) · resumo = linha fina (8314:1925) · data (8314:1729) · autor
 * Usuário (autor)   nome de exibição (8314:1921) · user meta art_cargo (8314:1922)
 *                   user meta art_avatar = foto da Mídia, usada por get_avatar() (8314:1919)
 *
 * Card reutilizável: artemis_blog_card( $post ) · artemis_blog_post_meta( $post ) · artemis_blog_author( $post ).
 * Consultas: artemis_blog_categories() · artemis_blog_cat_posts( $term ) · artemis_blog_cat_anchor( $term ) · artemis_blog_cat_featured_id().
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
   Customizer — rótulo do botão (8325:2696)
   -------------------------------------------------------------------------- */
add_filter( 'artemis_customizer_defaults', function ( $d ) {
	return $d + array(
		'art_blog_cat_more' => 'Ver todos os artigos',
	);
} );

add_filter( 'artemis_customizer_sections', function ( $s ) {
	$s['artemis_blog_categorias'] = array(
		__( 'Blog — Seções de categoria', 'artemis' ),
		array(
			'art_blog_cat_more' => array( __( 'Botão abaixo dos artigos de cada categoria', 'artemis' ), 'text' ),
		),
	);
	return $s;
} );

/* --------------------------------------------------------------------------
   Categoria: ordem na página do Blog (term meta _art_ordem)
   -------------------------------------------------------------------------- */
add_action( 'init', function () {
	register_term_meta( 'category', '_art_ordem', array(
		'type'              => 'integer',
		'single'            => true,
		'sanitize_callback' => 'absint',
	) );
} );

add_action( 'category_add_form_fields', function () {
	wp_nonce_field( 'art_ordem', 'art_ordem_nonce' );
	?>
	<div class="form-field">
		<label for="art_ordem"><?php esc_html_e( 'Ordem no Blog', 'artemis' ); ?></label>
		<input type="number" name="art_ordem" id="art_ordem" min="1" step="1" value="">
		<p><?php esc_html_e( 'Posição da seção desta categoria na página do Blog (1 = primeira). Vazio: depois das numeradas, em ordem alfabética.', 'artemis' ); ?></p>
	</div>
	<?php
} );

add_action( 'category_edit_form_fields', function ( $term ) {
	$ordem = (string) get_term_meta( $term->term_id, '_art_ordem', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="art_ordem"><?php esc_html_e( 'Ordem no Blog', 'artemis' ); ?></label></th>
		<td>
			<?php wp_nonce_field( 'art_ordem', 'art_ordem_nonce' ); ?>
			<input type="number" name="art_ordem" id="art_ordem" min="1" step="1" value="<?php echo esc_attr( $ordem ); ?>">
			<p class="description"><?php esc_html_e( 'Posição da seção desta categoria na página do Blog (1 = primeira). Vazio: depois das numeradas, em ordem alfabética.', 'artemis' ); ?></p>
		</td>
	</tr>
	<?php
} );

/** Grava a ordem ao criar/editar uma categoria. */
function artemis_blog_save_ordem( $term_id ) {
	if ( ! isset( $_POST['art_ordem_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['art_ordem_nonce'] ), 'art_ordem' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$raw = isset( $_POST['art_ordem'] ) ? trim( (string) wp_unslash( $_POST['art_ordem'] ) ) : '';
	if ( '' === $raw ) {
		delete_term_meta( $term_id, '_art_ordem' );
	} else {
		update_term_meta( $term_id, '_art_ordem', absint( $raw ) );
	}
}
add_action( 'created_category', 'artemis_blog_save_ordem' );
add_action( 'edited_category', 'artemis_blog_save_ordem' );

// Coluna "Ordem no Blog" na lista de categorias.
add_filter( 'manage_edit-category_columns', function ( $columns ) {
	$columns['art_ordem'] = __( 'Ordem no Blog', 'artemis' );
	return $columns;
} );
add_filter( 'manage_category_custom_column', function ( $content, $column, $term_id ) {
	if ( 'art_ordem' === $column ) {
		$content = esc_html( (string) get_term_meta( $term_id, '_art_ordem', true ) );
	}
	return $content;
}, 10, 3 );

/* --------------------------------------------------------------------------
   Usuário (autor): cargo (art_cargo) e foto (art_avatar) no perfil
   -------------------------------------------------------------------------- */
function artemis_blog_user_fields( $user ) {
	$cargo  = (string) get_user_meta( $user->ID, 'art_cargo', true );
	$avatar = (int) get_user_meta( $user->ID, 'art_avatar', true );
	$src    = $avatar ? wp_get_attachment_image_url( $avatar, 'thumbnail' ) : '';
	?>
	<h2><?php esc_html_e( 'Autor no Blog', 'artemis' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="art_cargo"><?php esc_html_e( 'Cargo', 'artemis' ); ?></label></th>
			<td>
				<input type="text" name="art_cargo" id="art_cargo" value="<?php echo esc_attr( $cargo ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Aparece abaixo do nome nos artigos do Blog.', 'artemis' ); ?></p>
			</td>
		</tr>
		<?php if ( current_user_can( 'upload_files' ) ) : ?>
			<tr>
				<th><?php esc_html_e( 'Foto', 'artemis' ); ?></th>
				<td>
					<span data-artemis-avatar-preview><?php echo $src ? '<img src="' . esc_url( $src ) . '" alt="" width="96" height="96" style="display:block;border-radius:50%;object-fit:cover;margin-bottom:8px">' : ''; ?></span>
					<input type="hidden" name="art_avatar" id="art_avatar" value="<?php echo esc_attr( $avatar ? (string) $avatar : '' ); ?>">
					<button type="button" class="button" data-artemis-avatar-pick><?php esc_html_e( 'Escolher foto', 'artemis' ); ?></button>
					<button type="button" class="button-link" data-artemis-avatar-clear><?php esc_html_e( 'Remover', 'artemis' ); ?></button>
					<p class="description"><?php esc_html_e( 'Foto quadrada (mín. 112×112). Sem foto, o site usa o Gravatar do e-mail.', 'artemis' ); ?></p>
				</td>
			</tr>
		<?php endif; ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'artemis_blog_user_fields' );
add_action( 'edit_user_profile', 'artemis_blog_user_fields' );

/** Grava cargo e foto (o nonce do formulário de perfil já foi verificado pelo WordPress). */
function artemis_blog_save_user_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	if ( isset( $_POST['art_cargo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- update-user_{id} verificado em user-edit.php.
		$cargo = sanitize_text_field( wp_unslash( $_POST['art_cargo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' === $cargo ) {
			delete_user_meta( $user_id, 'art_cargo' );
		} else {
			update_user_meta( $user_id, 'art_cargo', $cargo );
		}
	}
	if ( isset( $_POST['art_avatar'] ) && current_user_can( 'upload_files' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$avatar = absint( $_POST['art_avatar'] ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( $avatar && wp_attachment_is_image( $avatar ) ) {
			update_user_meta( $user_id, 'art_avatar', $avatar );
		} else {
			delete_user_meta( $user_id, 'art_avatar' );
		}
	}
}
add_action( 'personal_options_update', 'artemis_blog_save_user_fields' );
add_action( 'edit_user_profile_update', 'artemis_blog_save_user_fields' );

// Seletor da Mídia (wp.media) para a foto, só nas telas de perfil.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) || ! current_user_can( 'upload_files' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_register_script( 'artemis-blog-author-admin', false, array( 'media-views' ), ARTEMIS_VERSION, true );
	wp_enqueue_script( 'artemis-blog-author-admin' );
	wp_add_inline_script( 'artemis-blog-author-admin', <<<'JS'
document.addEventListener('click', function (e) {
  var pick = e.target.closest('[data-artemis-avatar-pick]');
  var clear = e.target.closest('[data-artemis-avatar-clear]');
  if (!pick && !clear) return;
  e.preventDefault();
  var input = document.getElementById('art_avatar');
  var box = document.querySelector('[data-artemis-avatar-preview]');
  if (clear) {
    input.value = '';
    box.innerHTML = '';
    return;
  }
  var frame = wp.media({ title: pick.textContent, library: { type: 'image' }, multiple: false });
  frame.on('select', function () {
    var att = frame.state().get('selection').first().toJSON();
    var img = document.createElement('img');
    img.src = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
    img.alt = '';
    img.width = 96;
    img.height = 96;
    img.style.cssText = 'display:block;border-radius:50%;object-fit:cover;margin-bottom:8px';
    input.value = att.id;
    box.innerHTML = '';
    box.appendChild(img);
  });
  frame.open();
});
JS
	);
} );

/** get_avatar() usa a foto do perfil (art_avatar) quando houver — no Blog, nos comentários e no admin. */
add_filter( 'pre_get_avatar_data', function ( $args, $id_or_email ) {
	$user_id = 0;
	if ( is_numeric( $id_or_email ) ) {
		$user_id = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$user_id = (int) $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$user_id = (int) $id_or_email->post_author;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$user_id = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$user    = get_user_by( 'email', $id_or_email );
		$user_id = $user ? (int) $user->ID : 0;
	}
	$avatar = $user_id ? (int) get_user_meta( $user_id, 'art_avatar', true ) : 0;
	if ( $avatar ) {
		$size = max( 1, (int) $args['size'] ) * 2; // @2x
		$url  = wp_get_attachment_image_url( $avatar, array( $size, $size ) );
		if ( $url ) {
			$args['url']          = $url;
			$args['found_avatar'] = true;
		}
	}
	return $args;
}, 10, 2 );

/* --------------------------------------------------------------------------
   Consultas
   -------------------------------------------------------------------------- */

/**
 * Post em destaque (seção 02 · 8314:1380): o fixado mais recente; sem fixados, o mais recente.
 * Fica fora das seções de categoria. Filtro artemis_blog_featured_id para a seção de destaque alinhar a regra.
 */
function artemis_blog_cat_featured_id() {
	static $id = null;
	if ( null === $id ) {
		$args   = array(
			'post_type'   => 'post',
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'fields'      => 'ids',
		);
		$sticky = array_filter( array_map( 'absint', (array) get_option( 'sticky_posts', array() ) ) );
		$found  = $sticky ? get_posts( $args + array( 'post__in' => $sticky ) ) : array();
		if ( ! $found ) {
			$found = get_posts( $args );
		}
		$id = (int) apply_filters( 'artemis_blog_featured_id', $found ? (int) $found[0] : 0 );
	}
	return $id;
}

/** Ordem da categoria no Blog (_art_ordem); sem ordem vai para o fim. */
function artemis_blog_cat_order( $term ) {
	$ordem = (string) get_term_meta( $term->term_id, '_art_ordem', true );
	return '' === $ordem ? PHP_INT_MAX : (int) $ordem;
}

/**
 * Categorias com posts publicados, na ordem do Blog (_art_ordem, depois nome).
 * "Sem categoria" (a padrão do WordPress) só entra se tiver ordem definida.
 */
function artemis_blog_categories() {
	$terms = get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
	) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	$default = (int) get_option( 'default_category' );
	$terms   = array_filter( $terms, function ( $term ) use ( $default ) {
		return (int) $term->term_id !== $default || PHP_INT_MAX !== artemis_blog_cat_order( $term );
	} );
	usort( $terms, function ( $a, $b ) {
		$by_order = artemis_blog_cat_order( $a ) <=> artemis_blog_cat_order( $b );
		return 0 !== $by_order ? $by_order : strnatcasecmp( remove_accents( $a->name ), remove_accents( $b->name ) );
	} );
	return array_values( $terms );
}

/** id da seção da categoria = âncora das abas do hero (#analises-de-caso); slug fora de [A-Za-z0-9_-] cai em categoria-<id>. */
function artemis_blog_cat_anchor( $term ) {
	return sanitize_html_class( $term->slug, 'categoria-' . (int) $term->term_id );
}

/** Os 3 posts mais recentes da categoria, sem o destaque. */
function artemis_blog_cat_posts( $term, $limit = 3 ) {
	$featured = artemis_blog_cat_featured_id();
	return get_posts( array(
		'post_type'           => 'post',
		'cat'                 => (int) $term->term_id,
		'numberposts'         => (int) $limit,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'post__not_in'        => $featured ? array( $featured ) : array(),
		'ignore_sticky_posts' => true,
	) );
}

/* --------------------------------------------------------------------------
   Card de post — reutilizável (o $block troca o prefixo BEM das classes)
   -------------------------------------------------------------------------- */

/** "13 de março, 2026" (8314:1729) — sempre em pt-BR, independente do idioma do site. */
function artemis_blog_date( $post ) {
	$meses = array( 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' );
	$mes   = (int) get_the_date( 'n', $post );
	return sprintf( '%d de %s, %s', (int) get_the_date( 'j', $post ), $meses[ max( 1, min( 12, $mes ) ) - 1 ], get_the_date( 'Y', $post ) );
}

/** Categoria exibida no card: a da seção (quando passada) ou a primeira do post. */
function artemis_blog_post_term( $post, $term = null ) {
	if ( $term instanceof WP_Term ) {
		return $term;
	}
	$cats = get_the_category( $post->ID );
	return $cats ? $cats[0] : null;
}

/** Linha "Categoria · 13 de março, 2026" (Meta Information 8314:1726), já escapada. */
function artemis_blog_post_meta( $post, $term = null, $block = 'post-card' ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$term = artemis_blog_post_term( $post, $term );
	$b    = sanitize_html_class( $block );
	$html = '<p class="' . $b . '__meta">';
	if ( $term ) {
		$html .= '<span class="' . $b . '__cat">' . esc_html( $term->name ) . '</span>';
		$html .= '<span class="' . $b . '__dot" aria-hidden="true">·</span>';
	}
	$html .= '<time class="' . $b . '__date" datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( artemis_blog_date( $post ) ) . '</time>';
	return $html . '</p>';
}

/** Autor: foto + nome + cargo (Author Info 8314:1918), já escapado. */
function artemis_blog_author( $post, $block = 'post-card' ) {
	$post = get_post( $post );
	if ( ! $post || ! $post->post_author ) {
		return '';
	}
	$b      = sanitize_html_class( $block );
	$author = (int) $post->post_author;
	$cargo  = (string) get_user_meta( $author, 'art_cargo', true );
	$avatar = get_avatar( $author, 56, '', '', array( 'class' => $b . '__avatar' ) );

	$html  = '<div class="' . $b . '__author">';
	$html .= $avatar ? $avatar : '';
	$html .= '<p class="' . $b . '__author-details">';
	$html .= '<span class="' . $b . '__author-name">' . esc_html( get_the_author_meta( 'display_name', $author ) ) . '</span>';
	if ( '' !== $cargo ) {
		$html .= '<span class="' . $b . '__author-role">' . esc_html( $cargo ) . '</span>';
	}
	return $html . '</p></div>';
}

/** Linha fina (8314:1925): o resumo do post; sem resumo, o começo do texto. */
function artemis_blog_excerpt( $post ) {
	if ( has_excerpt( $post ) ) {
		return trim( wp_strip_all_tags( $post->post_excerpt ) );
	}
	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 18, '…' );
}

/**
 * Card de post (Article Content 8314:1710) — mesma marcação do partial, já escapada.
 * $args: term (WP_Term exibida na meta), heading (h2|h3|h4).
 */
function artemis_blog_card( $post, $args = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$args    = wp_parse_args( $args, array(
		'term'    => null,
		'heading' => 'h3',
	) );
	$heading = in_array( $args['heading'], array( 'h2', 'h3', 'h4' ), true ) ? $args['heading'] : 'h3';
	$excerpt = artemis_blog_excerpt( $post );

	$html  = '<article class="post-card">';
	$html .= '<div class="post-card__text">';
	$html .= artemis_blog_post_meta( $post, $args['term'] );
	$html .= '<div class="post-card__info">';
	$html .= '<' . $heading . ' class="post-card__title"><a class="post-card__link" href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></' . $heading . '>';
	if ( '' !== $excerpt ) {
		$html .= '<p class="post-card__excerpt">' . esc_html( $excerpt ) . '</p>';
	}
	$html .= '</div>';
	$html .= artemis_blog_author( $post );
	$html .= '</div>';
	return $html . '</article>';
}

/* --------------------------------------------------------------------------
   Seed — 5 categorias do Figma (ordem 1–5), 3 posts por categoria + 1 fixado (destaque)
   Figma usa o mesmo artigo de exemplo em todos os cards (8314:1713 / 8314:1925 / 8314:1729);
   idempotente pela chave _artemis_seed_key (títulos repetidos não servem de chave).
   -------------------------------------------------------------------------- */

/** Cria (uma vez) um post do seed identificado por $key. Retorna o ID. */
function artemis_blog_seed_post( $key, array $data, array &$log ) {
	$found = get_posts( array(
		'post_type'   => 'post',
		'post_status' => 'any',
		'meta_key'    => '_artemis_seed_key',
		'meta_value'  => $key,
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $found ) {
		return (int) $found[0];
	}
	$id = wp_insert_post( array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $data['title'],
		'post_excerpt'  => $data['excerpt'],
		'post_content'  => "<!-- wp:paragraph -->\n<p>" . esc_html( $data['excerpt'] ) . "</p>\n<!-- /wp:paragraph -->",
		'post_date'     => $data['date'],
		'post_author'   => $data['author'],
		'post_category' => array( $data['cat'] ),
	) );
	if ( ! $id || is_wp_error( $id ) ) {
		$log[] = 'Falha ao criar o post ' . $key;
		return 0;
	}
	update_post_meta( $id, '_artemis_seed_key', $key );
	if ( $data['image'] ) {
		set_post_thumbnail( $id, $data['image'] );
	}
	$log[] = sprintf( 'Post criado (%s): %s', $key, $data['title'] );
	return (int) $id;
}

add_filter( 'artemis_seed', function ( $log ) {
	$categorias = array(
		'analises-de-caso'    => 'Análises de caso',
		'implementacao-de-ia' => 'Implementação de IA',
		'ia-na-operacao'      => 'IA na operação',
		'seo-e-ai-search'     => 'SEO e AI Search',
		'marketing-com-ia'    => 'Marketing com IA',
	);
	$titulo  = 'Anatomia do WControl: como se valida um documento de mil páginas';
	$linha   = 'O dia em que a IA discordou do engenheiro — e estava certa';
	$capa    = artemis_seed_media( 'images/blog-03-categoria/capa-wcontrol.png', 'Capa — Anatomia do WControl' );
	$cat_ids = array();

	// Categorias com ordem 1–5 (não sobrescreve ordem já definida no admin).
	$ordem = 0;
	foreach ( $categorias as $slug => $nome ) {
		++$ordem;
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term ) {
			$new = wp_insert_term( $nome, 'category', array( 'slug' => $slug ) );
			if ( is_wp_error( $new ) ) {
				$log[] = 'Falha ao criar a categoria ' . $nome;
				continue;
			}
			$term_id = (int) $new['term_id'];
			$log[]   = 'Categoria criada: ' . $nome;
		} else {
			$term_id = (int) $term->term_id;
		}
		if ( '' === (string) get_term_meta( $term_id, '_art_ordem', true ) ) {
			update_term_meta( $term_id, '_art_ordem', $ordem );
		}
		$cat_ids[ $slug ] = $term_id;
	}

	// Autor: o primeiro administrador, com cargo e foto do Figma (só preenche o que estiver vazio).
	$admins = get_users( array(
		'role'    => 'administrator',
		'number'  => 1,
		'orderby' => 'ID',
		'fields'  => 'ID',
	) );
	$author = $admins ? (int) $admins[0] : get_current_user_id();
	if ( $author ) {
		if ( '' === (string) get_user_meta( $author, 'art_cargo', true ) ) {
			update_user_meta( $author, 'art_cargo', 'Sócio-fundador' );
		}
		if ( ! get_user_meta( $author, 'art_avatar', true ) ) {
			$foto = artemis_seed_media( 'images/blog-03-categoria/avatar-autor.png', 'Foto — autor do blog' );
			if ( $foto ) {
				update_user_meta( $author, 'art_avatar', $foto );
			}
		}
	}

	if ( ! $cat_ids ) {
		return $log;
	}

	// Destaque: post fixado (reaproveita o de mesmo título se a seção de destaque já o criou).
	$destaque = get_posts( array(
		'post_type'   => 'post',
		'post_status' => 'any',
		'meta_key'    => '_artemis_seed_key',
		'meta_value'  => 'blog-destaque',
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( ! $destaque ) {
		$destaque = get_posts( array(
			'post_type'   => 'post',
			'post_status' => 'any',
			'title'       => $titulo,
			'numberposts' => 1,
			'fields'      => 'ids',
		) );
		if ( $destaque ) {
			update_post_meta( (int) $destaque[0], '_artemis_seed_key', 'blog-destaque' );
		}
	}
	$destaque_id = $destaque ? (int) $destaque[0] : artemis_blog_seed_post( 'blog-destaque', array(
		'title'   => $titulo,
		'excerpt' => $linha,
		'date'    => '2026-03-13 12:00:00',
		'author'  => $author,
		'cat'     => reset( $cat_ids ),
		'image'   => $capa,
	), $log );
	if ( $destaque_id && ! is_sticky( $destaque_id ) ) {
		stick_post( $destaque_id );
	}

	// 3 posts por categoria (mesmo conteúdo do Figma; horários diferentes = ordem estável).
	foreach ( $cat_ids as $slug => $term_id ) {
		for ( $i = 1; $i <= 3; $i++ ) {
			artemis_blog_seed_post( 'blog-' . $slug . '-' . $i, array(
				'title'   => $titulo,
				'excerpt' => $linha,
				'date'    => sprintf( '2026-03-13 %02d:00:00', 12 - $i ),
				'author'  => $author,
				'cat'     => $term_id,
				'image'   => $capa,
			), $log );
		}
	}

	// Links "Ver todos os artigos" em /blog/categoria/<slug>/, como no estático (só com links permanentes ativos e base vazia).
	if ( get_option( 'permalink_structure' ) && '' === (string) get_option( 'category_base' ) ) {
		update_option( 'category_base', 'blog/categoria' );
		flush_rewrite_rules( false );
		$log[] = 'Base das categorias: /blog/categoria/.';
	}
	return $log;
} );
