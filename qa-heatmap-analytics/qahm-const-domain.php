<?php

// organic search
const SEARCH_ENGINES = array(
	'GOOGLE'            => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '1',
	),
	'YAHOO_JP'          => array(
		'NAME'         => 'yahoo.co.jp',
		'DOMAIN'       => 'search.yahoo.co.jp',
		'QUERY_PERM'   => 'p',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '2',
	),
	'YAHOO_COM'         => array(
		'NAME'         => 'yahoo',
		'DOMAIN'       => 'search.yahoo.com',
		'QUERY_PERM'   => 'p',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '3',
	),
	'BING'              => array(
		'NAME'         => 'bing',
		'DOMAIN'       => 'www.bing.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '4',
	),
	'GOO.NE'            => array(
		'NAME'         => 'goo.ne.jp',
		'DOMAIN'       => 'search.goo.ne.jp',
		'QUERY_PERM'   => 'MT',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '5',
	),
	'RAKUTEN'           => array(
		'NAME'         => 'Rakuten',
		'DOMAIN'       => 'websearch.rakuten.co.jp',
		'QUERY_PERM'   => 'qt',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'BIGLOBE'           => array(
		'NAME'         => 'Biglobe',
		'DOMAIN'       => 'search.biglobe.ne.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'AUONE'             => array(
		'NAME'         => 'Auone',
		'DOMAIN'       => 'search.auone.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'SEARCH.SMT.DOCOMO' => array(
		'NAME'         => 'search.smt.docomo',
		'DOMAIN'       => 'search.smt.docomo.ne.jp',
		'QUERY_PERM'   => 'MT',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'FUJITSU'           => array(
		'NAME'         => 'fujitsu',
		'DOMAIN'       => 'fmworld.net',
		'QUERY_PERM'   => 'q,Text',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'NIFTY'             => array(
		'NAME'         => 'nifty',
		'DOMAIN'       => 'nifty.com',
		'QUERY_PERM'   => 'q,Text',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'SO-NET'            => array(
		'NAME'         => 'so-net',
		'DOMAIN'       => 'so-net.ne.jp',
		'QUERY_PERM'   => 'query',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'ODN'               => array(
		'NAME'         => 'odn',
		'DOMAIN'       => 'odn.jword.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'EXCITE'            => array(
		'NAME'         => 'excite',
		'DOMAIN'       => 'excite.co.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'SLEIPNIR'          => array(
		'NAME'         => 'sleipnir',
		'DOMAIN'       => 'fenrir-inc.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'LUNASCAPE'         => array(
		'NAME'         => 'lunascape',
		'DOMAIN'       => 'luna.tv',
		'QUERY_PERM'   => 'keyword,q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'MYJCOM'            => array(
		'NAME'         => 'myjcom',
		'DOMAIN'       => 'myjcom.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'LIVEDOOR'          => array(
		'NAME'         => 'livedoor',
		'DOMAIN'       => 'livedoor.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'ECNAVI'            => array(
		'NAME'         => 'ecnavi',
		'DOMAIN'       => 'ecnavi.jp',
		'QUERY_PERM'   => 'Keywords',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'CYBOZU'            => array(
		'NAME'         => 'cybozu',
		'DOMAIN'       => 'cybozu.net',
		'QUERY_PERM'   => 'Keywords',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'PEX'               => array(
		'NAME'         => 'pex',
		'DOMAIN'       => 'pex.jp',
		'QUERY_PERM'   => 'keyword',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'HAO123'            => array(
		'NAME'         => 'hao123',
		'DOMAIN'       => 'hao123.com',
		'QUERY_PERM'   => 'query',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'FOOOOO'            => array(
		'NAME'         => 'fooooo',
		'DOMAIN'       => 'fooooo.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'JWORD'             => array(
		'NAME'         => 'jword',
		'DOMAIN'       => 'jword.jp',
		'QUERY_PERM'   => 'name,q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'ASWIDGET'          => array(
		'NAME'         => 'aswidget',
		'DOMAIN'       => 'aswidget.com',
		'QUERY_PERM'   => 'Keywords',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'WOW'               => array(
		'NAME'         => 'wow',
		'DOMAIN'       => 'jp.wow.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'ADINGO.JP'         => array(
		'NAME'         => 'adingo.jp',
		'DOMAIN'       => 'adingo.jp',
		'QUERY_PERM'   => 'Keywords',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'T-COM'             => array(
		'NAME'         => 't-com',
		'DOMAIN'       => 't-com.ne.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'JIQOO'             => array(
		'NAME'         => 'jiqoo',
		'DOMAIN'       => 'jiqoo.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'WINDOWSSEARCH'     => array(
		'NAME'         => 'windowssearch',
		'DOMAIN'       => 'windowssearch.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'DUCKDUCKGO'        => array(
		'NAME'         => 'duckduckgo',
		'DOMAIN'       => 'duckduckgo.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'KINGSOFT'          => array(
		'NAME'         => 'kingsoft',
		'DOMAIN'       => 'kingsoft.jp',
		'QUERY_PERM'   => 'keyword',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'MYSEARCH'          => array(
		'NAME'         => 'mysearch',
		'DOMAIN'       => 'mysearch.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'ASK'               => array(
		'NAME'         => 'ask',
		'DOMAIN'       => 'ask.com',
		'QUERY_PERM'   => 'searchfor',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'UNISEARCH'         => array(
		'NAME'         => 'unisearch',
		'DOMAIN'       => 'unisearch.jp',
		'QUERY_PERM'   => 'keyword',
		'NOT_PROVIDED' => '0',
		'SOURCE_ID'    => '0',
	),
	'GOOGLE_AC'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ac',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '11',
	),
	'GOOGLE_AD'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ad',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '12',
	),
	'GOOGLE_AE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ae',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '13',
	),
	'GOOGLE_AF'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.af',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '14',
	),
	'GOOGLE_AG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ag',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '15',
	),
	'GOOGLE_AI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.off.ai',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '16',
	),
	'GOOGLE_AM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.am',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '17',
	),
	'GOOGLE_AO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ao',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '18',
	),
	'GOOGLE_AR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ar',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '19',
	),
	'GOOGLE_AS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.as',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '20',
	),
	'GOOGLE_AT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.at',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '21',
	),
	'GOOGLE_AU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.au',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '22',
	),
	'GOOGLE_AZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.az',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '23',
	),
	'GOOGLE_BA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ba',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '24',
	),
	'GOOGLE_BD'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.bd',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '25',
	),
	'GOOGLE_BE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.be',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '26',
	),
	'GOOGLE_BG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.bg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '27',
	),
	'GOOGLE_BH'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.bh',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '28',
	),
	'GOOGLE_BI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.bi',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '29',
	),
	'GOOGLE_BJ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.bj',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '30',
	),
	'GOOGLE_BN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.bn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '31',
	),
	'GOOGLE_BO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.bo',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '32',
	),
	'GOOGLE_BR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.br',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '33',
	),
	'GOOGLE_BS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.bs',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '34',
	),
	'GOOGLE_BW'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.bw',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '35',
	),
	'GOOGLE_BY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.by',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '36',
	),
	'GOOGLE_BZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.bz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '37',
	),
	'GOOGLE_CA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ca',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '38',
	),
	'GOOGLE_CD'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.cd',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '39',
	),
	'GOOGLE_F/'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.cf/',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '40',
	),
	'GOOGLE_CG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.cg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '41',
	),
	'GOOGLE_CH'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ch',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '42',
	),
	'GOOGLE_CI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ci',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '43',
	),
	'GOOGLE_CK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ck',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '44',
	),
	'GOOGLE_CL'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.cl',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '45',
	),
	'GOOGLE_CN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.cn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '46',
	),
	'GOOGLE_CO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.co',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '47',
	),
	'GOOGLE_CR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.cr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '48',
	),
	'GOOGLE_CU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.cu',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '49',
	),
	'GOOGLE_CY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.cy',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '50',
	),
	'GOOGLE_CZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.cz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '51',
	),
	'GOOGLE_DE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.de',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '52',
	),
	'GOOGLE_DJ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.dj',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '53',
	),
	'GOOGLE_DK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.dk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '54',
	),
	'GOOGLE_DM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.dm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '55',
	),
	'GOOGLE_DO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.do',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '56',
	),
	'GOOGLE_DZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.dz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '57',
	),
	'GOOGLE_EC'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ec',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '58',
	),
	'GOOGLE_EE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ee',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '59',
	),
	'GOOGLE_EG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.eg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '60',
	),
	'GOOGLE_ES'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.es',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '61',
	),
	'GOOGLE_ET'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.et',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '62',
	),
	'GOOGLE_FI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.fi',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '63',
	),
	'GOOGLE_FJ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.fj',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '64',
	),
	'GOOGLE_FM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.fm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '65',
	),
	'GOOGLE_FR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.fr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '66',
	),
	'GOOGLE_GD'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gd',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '67',
	),
	'GOOGLE_GE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ge',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '68',
	),
	'GOOGLE_GF'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gf',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '69',
	),
	'GOOGLE_GG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '70',
	),
	'GOOGLE_GH'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.gh',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '71',
	),
	'GOOGLE_GI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.gi',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '72',
	),
	'GOOGLE_GL'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gl',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '73',
	),
	'GOOGLE_GM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '74',
	),
	'GOOGLE_GP'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '75',
	),
	'GOOGLE_GR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '76',
	),
	'GOOGLE_GT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.gt',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '77',
	),
	'GOOGLE_GY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.gy',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '78',
	),
	'GOOGLE_HK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.hk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '79',
	),
	'GOOGLE_HN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.hn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '80',
	),
	'GOOGLE_HR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.hr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '81',
	),
	'GOOGLE_HT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ht',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '82',
	),
	'GOOGLE_HU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.hu',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '83',
	),
	'GOOGLE_ID'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.id',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '84',
	),
	'GOOGLE_IE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ie',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '85',
	),
	'GOOGLE_IL'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.il',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '86',
	),
	'GOOGLE_IM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.im',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '87',
	),
	'GOOGLE_IN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.in',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '88',
	),
	'GOOGLE_IS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.is',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '89',
	),
	'GOOGLE_IT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.it',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '90',
	),
	'GOOGLE_JE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.je',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '91',
	),
	'GOOGLE_JM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.jm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '92',
	),
	'GOOGLE_JO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.jo',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '93',
	),
	'GOOGLE_JP'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.jp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '94',
	),
	'GOOGLE_KE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ke',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '95',
	),
	'GOOGLE_KG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.kg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '96',
	),
	'GOOGLE_H/'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.kh/',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '97',
	),
	'GOOGLE_KI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ki',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '98',
	),
	'GOOGLE_KR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.kr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '99',
	),
	'GOOGLE_KW'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.kw',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '100',
	),
	'GOOGLE_KZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.kz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '101',
	),
	'GOOGLE_LA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.la',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '102',
	),
	'GOOGLE_LB'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.lb',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '103',
	),
	'GOOGLE_LC'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.lc',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '104',
	),
	'GOOGLE_LI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.li',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '105',
	),
	'GOOGLE_LK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.lk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '106',
	),
	'GOOGLE_LS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ls',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '107',
	),
	'GOOGLE_LT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.lt',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '108',
	),
	'GOOGLE_LU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.lu',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '109',
	),
	'GOOGLE_LV'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.lv',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '110',
	),
	'GOOGLE_LY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ly',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '111',
	),
	'GOOGLE_MA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ma',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '112',
	),
	'GOOGLE_MD'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.md',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '113',
	),
	'GOOGLE_ME'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.me',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '114',
	),
	'GOOGLE_MG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.mg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '115',
	),
	'GOOGLE_MK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.mk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '116',
	),
	'GOOGLE_MN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.mn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '117',
	),
	'GOOGLE_MS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ms',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '118',
	),
	'GOOGLE_MT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.mt',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '119',
	),
	'GOOGLE_MU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.mu',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '120',
	),
	'GOOGLE_MV'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.mv',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '121',
	),
	'GOOGLE_MW'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.mw',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '122',
	),
	'GOOGLE_MX'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.mx',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '123',
	),
	'GOOGLE_MY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.my',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '124',
	),
	'GOOGLE_MZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.mz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '125',
	),
	'GOOGLE_NA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.na',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '126',
	),
	'GOOGLE_NF'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.nf',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '127',
	),
	'GOOGLE_NG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ng',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '128',
	),
	'GOOGLE_NI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ni',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '129',
	),
	'GOOGLE_NL'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.nl',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '130',
	),
	'GOOGLE_NO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.no',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '131',
	),
	'GOOGLE_NP'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.np',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '132',
	),
	'GOOGLE_NR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.nr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '133',
	),
	'GOOGLE_NU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.nu',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '134',
	),
	'GOOGLE_NZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.nz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '135',
	),
	'GOOGLE_OM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.om',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '136',
	),
	'GOOGLE_PA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.pa',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '137',
	),
	'GOOGLE_PE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.pe',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '138',
	),
	'GOOGLE_PH'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ph',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '139',
	),
	'GOOGLE_PK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.pk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '140',
	),
	'GOOGLE_PL'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.pl',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '141',
	),
	'GOOGLE_PN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.pn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '142',
	),
	'GOOGLE_PR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.pr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '143',
	),
	'GOOGLE_S/'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ps/',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '144',
	),
	'GOOGLE_PT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.pt',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '145',
	),
	'GOOGLE_PY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.py',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '146',
	),
	'GOOGLE_QA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.qa',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '147',
	),
	'GOOGLE_RO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ro',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '148',
	),
	'GOOGLE_RS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.rs',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '149',
	),
	'GOOGLE_RU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ru',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '150',
	),
	'GOOGLE_RW'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.rw',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '151',
	),
	'GOOGLE_SA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.sa',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '152',
	),
	'GOOGLE_SB'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.sb',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '153',
	),
	'GOOGLE_SC'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.sc',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '154',
	),
	'GOOGLE_SE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.se',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '155',
	),
	'GOOGLE_SG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.sg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '156',
	),
	'GOOGLE_SH'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.sh',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '157',
	),
	'GOOGLE_SI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.si',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '158',
	),
	'GOOGLE_SK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.sk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '159',
	),
	'GOOGLE_SL'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.sl',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '160',
	),
	'GOOGLE_SM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.sm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '161',
	),
	'GOOGLE_SN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.sn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '162',
	),
	'GOOGLE_ST'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.st',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '163',
	),
	'GOOGLE_SV'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.sv',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '164',
	),
	'GOOGLE_TH'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.th',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '165',
	),
	'GOOGLE_TJ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.tj',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '166',
	),
	'GOOGLE_TK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.tk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '167',
	),
	'GOOGLE_TM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.tm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '168',
	),
	'GOOGLE_TO'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.to',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '169',
	),
	'GOOGLE_TP'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.tp',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '170',
	),
	'GOOGLE_TR'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.tr',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '171',
	),
	'GOOGLE_TT'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.tt',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '172',
	),
	'GOOGLE_TW'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.tw',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '173',
	),
	'GOOGLE_TZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.tz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '174',
	),
	'GOOGLE_UA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.ua',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '175',
	),
	'GOOGLE_UG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ug',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '176',
	),
	'GOOGLE_UK'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.uk',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '177',
	),
	'GOOGLE_UY'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.uy',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '178',
	),
	'GOOGLE_UZ'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.uz',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '179',
	),
	'GOOGLE_VC'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.vc',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '180',
	),
	'GOOGLE_VE'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.ve',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '181',
	),
	'GOOGLE_VG'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.vg',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '182',
	),
	'GOOGLE_VI'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.vi',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '183',
	),
	'GOOGLE_VN'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.com.vn',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '184',
	),
	'GOOGLE_VU'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.vu',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '185',
	),
	'GOOGLE_WS'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.ws',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '186',
	),
	'GOOGLE_ZA'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.za',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '187',
	),
	'GOOGLE_ZM'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.zm',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '188',
	),
	'GOOGLE_ZW'         => array(
		'NAME'         => 'google',
		'DOMAIN'       => 'www.google.co.zw',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '189',
	),
	'BING_COM'         => array(
		'NAME'         => 'bing',
		'DOMAIN'       => 'bing.com',
		'QUERY_PERM'   => 'q',
		'NOT_PROVIDED' => '1',
		'SOURCE_ID'    => '0',
	),
);

// GCLID
const GCLID = array(
	'GOOGLEADV' => array(
		'NAME'      => 'Google',
		'DOMAIN'    => 'www.google.com',
		'SOURCE_ID' => '6',
	),
);

// social
const SOCIAL_DOMAIN = array(
	'TWITTER'   => array(
		'NAME'      => 'twitter',
		'DOMAIN'    => 'twitter.com',
		'SOURCE_ID' => '7',
	),
	'T.CO'      => array(
		'NAME'      => 'twitter',
		'DOMAIN'    => 't.co',
		'SOURCE_ID' => '8',
	),
	'FACEBOOK'  => array(
		'NAME'      => 'facebook',
		'DOMAIN'    => 'facebook.com',
		'SOURCE_ID' => '9',
	),
	'INSTAGRAM' => array(
		'NAME'      => 'instagram',
		'DOMAIN'    => 'instagram.com',
		'SOURCE_ID' => '10',
	),
);

// utm_media default id
const UTM_MEDIUM_ID = array(
	'ORGANIC'   => 1,
	'GCLID'     => 2,
	'DISPLAY'   => 5,
	'AFFILIATE' => 12,
	'SOCIAL'    => 13,
	'EMAIL'     => 19,
);