<?php

namespace App\Twig;

use App\Entity\Product\ProductImage;
use App\Entity\Taxonomy\TaxonImage;
use BitBag\SyliusElasticsearchPlugin\Controller\RequestDataHandler\DataHandlerInterface;
use BitBag\SyliusElasticsearchPlugin\Finder\ShopProductsFinderInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use MonsieurBiz\SyliusMenuPlugin\Entity\MenuItem;
use MonsieurBiz\SyliusSettingsPlugin\Settings\RegistryInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use NumberFormatter;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductRepository;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\Taxon;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Dflydev\DotAccessData\Data;

class AppExtension extends AbstractExtension
{
	private $cacheManager;
	private $taxonRepository;
	private $productRepository;
	private $router;
	private $shopProductsFinder;
	private $settings;
	private $translations;
	private $baseurl;


    public function __construct(
        UrlGeneratorInterface $router,
        CacheManager $cacheManager,
        TaxonRepository $taxonRepository,
        ProductRepository $productRepository,
        ShopProductsFinderInterface $shopProductsFinder,
        ContainerInterface $container,
        RegistryInterface $settingsRegistry
    )
    {
        $this->router = $router;
        $this->cacheManager = $cacheManager;
        $this->taxonRepository = $taxonRepository;
        $this->productRepository = $productRepository;
        $this->shopProductsFinder = $shopProductsFinder;

		if( php_sapi_name() == "cli" )
			return;

	    // Settings
	    $this->settings = [];
	    $channel = $container->get('sylius.context.channel')->getChannel();

	    /** @var SettingsInterface[] $settings */
	    if ($allSettings = $settingsRegistry->getAllSettings()) {
		    foreach ($allSettings as $setting){
			    $this->settings = array_merge($this->settings, $setting->getSettingsValuesByChannelAndLocale($channel));
		    }
	    }

	    $this->baseurl = trim($this->router->generate('sylius_shop_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL),'/');

		$this->translations = new Data(json_decode(file_get_contents(__DIR__.'/../../translations/'.$this->settings['theme'].'.json'), true));
    }

    public function getFilters()
    {
        return [
            new TwigFilter('truncate', [$this, 'truncate']),
            new TwigFilter('within', [$this, 'within']),
            new TwigFilter('asset_url', [$this, 'assetUrl']),
            new TwigFilter('url', [$this, 'generateUrl']),
            new TwigFilter('stylesheet_tag', [$this, 'stylesheetTag'], ['is_safe' => ['html']]),
            new TwigFilter('script_tag', [$this, 'scriptTag'], ['is_safe' => ['html']]),
            new TwigFilter('img_url', [$this, 'imgUrl'], ['is_safe' => ['html']]),
            new TwigFilter('append', [$this, 'append']),
            new TwigFilter('prepend', [$this, 'prepend']),
            new TwigFilter('handle', [$this, 'handle']),
            new TwigFilter('handleize', [$this, 'handle']),
            new TwigFilter('hmac_sha1', [$this, 'hmac_sha1']),
            new TwigFilter('t', [$this, 'translate'], ['is_safe' => ['html']]),
            new TwigFilter('strip', 'strip_tags'),
            new TwigFilter('strip_html', 'strip_tags'),
            new TwigFilter('product_img_url', [$this, 'productImgUrl']),
            new TwigFilter('money_without_currency', [$this, 'moneyWithoutCurrency']),
            new TwigFilter('money', [$this, 'moneyWithCurrency']),
            new TwigFilter('weight_with_unit', [$this, 'weightWithUnit']),
            new TwigFilter('divided_by', [$this, 'dividedBy']),
            new TwigFilter('plus', [$this, 'plus']),
            new TwigFilter('minus', [$this, 'minus']),
            new TwigFilter('json_decode', 'json_decode'),
            new TwigFilter('size',  [$this, 'size']),
            new TwigFilter('json', 'json_encode', ['is_safe' => ['html']]),
            new TwigFilter('hasProducts', [$this, 'hasProducts']),
            new TwigFilter('cache', [$this, 'addToCache']),
            new TwigFilter('strip_newlines', [$this, 'stripNewlines']),
            new TwigFilter('object_map', [$this, 'objectMap']),
            new TwigFilter('remove', [$this, 'remove']),
            new TwigFilter('custom_replace', [$this, 'customReplace']),
            new TwigFilter('tva', [$this, 'tva']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('area', [$this, 'calculateArea']),
            new TwigFunction('all_products', [$this, 'allProducts']),
            new TwigFunction('collections', [$this, 'collections']),
            new TwigFunction('paginate_next_url', [$this, 'paginateNextUrl']),
        ];
    }

    public function size($item)
    {
        if( !$item )
            return 0;

        return is_countable($item)?count($item):0;
    }

    public function remove($string, $remove)
    {
        return str_replace($remove, '', $string);
    }

    public function customReplace($string)
    {
        $replacements = [
            'Iv' => 'iV',
            'Id' => 'ID',
            'Up!' => 'up!',
            'E-Up!' => 'e-up!',
            'Golf Sw' => 'Golf SW',
            'Passat Sw' => 'Passat SW'
        ];

        return strtr($string, $replacements);
    }

    public function tva($price)
    {
        return $price * 0.20;
    }

    public function objectMap($object, $key)
    {
        return array_filter(array_map(function ($a) use($key){

            $method = 'get'.ucfirst($key);

            if( method_exists($a, $method) )
                return $a->$method();

            return null;
        }, $object));
    }

    public function stripNewlines($string)
    {
        return str_replace("\n", '', $string);
    }

    public function addToCache($url)
    {
		$key = md5($url).'.jpg';

        if( !file_exists($key) ){

			if( !is_dir(__DIR__.'/../../public/media/cache/gmap/') )
				mkdir(__DIR__.'/../../public/media/cache/gmap/', 0755);

            if( $content = @file_get_contents($url) )
                file_put_contents(__DIR__.'/../../public/media/cache/gmap/'.$key, $content);
        }

	    return '/media/cache/gmap/'.$key;
    }

    /**
     * @param $taxon
     * @param $vehicle
     * @return bool
     */
    public function hasProducts(Taxon $taxon, ?Taxon $vehicle)
    {
        if( !$vehicle )
            return true;

        $data = [
            'name' => '',
            'sort' =>  [
                'taxon_position_'.$taxon->getCode() =>
                    ["order"=> "asc","unmapped_type"=>"keyword"]
            ],
            'limit' => 1,
            'page' => 1,
            'taxon' => [$taxon, $vehicle]
        ];

        return count($this->shopProductsFinder->find($data)) > 0;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function paginateNextUrl(Request $request)
    {
        $page = $request->get('page', 1);

        if( !is_int($page) )
            $page = 1;

        $params = $request->get('_route_params');
        $params = array_merge($params, $request->query->all());
        $params = array_merge($params, ['page'=>$page+1]);

        return $this->router->generate($request->get('_route'), $params);
    }

    /**
     * @param $entity
     * @return string
     */
    public function generateUrl($entity)
    {
        if( $entity instanceof MenuItem ){
            return $entity->getUrl();
        }
        elseif( $entity instanceof ProductVariant ){
            $product = $entity->getProduct();
            return $this->router->generate('sylius_shop_product_show', ['slug' => $product->getSlug()]).'?variant='.$entity->getCode();
        }
        if( $entity instanceof Product )
            return $this->router->generate('sylius_shop_product_show', ['slug' => $entity->getSlug()]);
        elseif ( $entity instanceof Taxon )
            return $this->router->generate('sylius_shop_collection', ['slug' => $entity->getSlug()]);

        return '';
    }

    public function allProducts($taxonCode, $count=10)
    {
        if( $taxon = $this->taxonRepository->findOneBy(['code'=>$taxonCode]) ) {

            $qb = $this->productRepository->createQueryBuilder('p');

            $qb->innerJoin('p.productTaxons', 't')
                ->andWhere('t.taxon = :taxon')
                ->setParameter('taxon', $taxon);

            $qb->setMaxResults($count);

            return $qb->getQuery()->getResult();
        }

        return [];
    }

    public function collections($taxonCode)
    {
        if( $taxon = $this->taxonRepository->findOneBy(['code'=>$taxonCode]) )
            return $taxon;

        return [];
    }

    public function minus(?int $base, int $value=0): int
    {
        if( !$base )
            return $value;

        return $base - $value;
    }

    public function plus(?int $base, int $value=0): int
    {
        if( !$base )
            return $value;

        return $base + $value;
    }

    public function calculateArea(int $width, int $length): int
    {
        return $width * $length;
    }

    public function dividedBy(?float $value, float $divide): ?int
    {
        if( !$value )
            return 0;

        return $value / $divide;
    }

    public function weightWithUnit(string $value, $unit='kg'): string
    {
        return $value.' '.$unit;
    }

    public function truncate(?string $text, $offset=0, $length=0): string
    {
        if( !$text )
            return '';

        if( $length == 0 ){
            $length = $offset;
            $offset = 0;
        }

        return strlen($text) > $length ? substr($text,$offset, $length)."..." : $text;
    }

    public function moneyWithoutCurrency(?int $price): ?string
    {
        if( !$price )
            return '';

        $fmt = new NumberFormatter( 'fr_FR', NumberFormatter::DECIMAL );
        return $fmt->formatCurrency($price/100, "EUR");
    }

    public function moneyWithCurrency(?int $price): string
    {
        if( !$price )
            return '';

        //Todo: get fr_FR and EUR from global option
        $fmt = new NumberFormatter( 'fr_FR', NumberFormatter::CURRENCY );
        return $fmt->formatCurrency($price/100, "EUR");
    }

    public function translate(string $text, $context=[]): string
    {
        $translation = $this->translations->get($text, $text);

        if( is_array($translation) ){

            if( ($context['count']??0)>1 )
                $translation = $translation['other']??$text;
            else
                $translation = $translation['one']??$text;
        }

        foreach ($context as $key=>$value){
            $translation = str_replace('{{ '.$key.' }}', $value, $translation);
            $translation = str_replace('{{'.$key.'}}', $value, $translation);
        }

        return $translation;
    }

    public function assetUrl(string $filename): string
    {
        return '/assets/shop/'.$filename;
    }

    public function productImgUrl(string $filename): string
    {
        return $filename;
    }

    public function stylesheetTag(string $filepath): string
    {
        return '<link rel="stylesheet" type="text/css" href="'.$filepath.'"/>';
    }

    public function scriptTag(string $filepath): string
    {
        return '<script src="'.$filepath.'"></script>';
    }

    public function imgUrl($filepath, $size): string
    {
        if(substr($size, 0, 1) == 'x' )
            $size = '0'.$size;
        elseif (substr($size, 0, -1) == 'x' )
            $size = $size.'0';

	    $size = str_replace("\n","", strip_tags($size));
        $size = explode('x', $size);

		if( !$filepath )
			return '';

        if( $filepath instanceof TaxonImage )
            $filepath = $filepath->getPath();
        elseif( $filepath instanceof ProductImage )
            $filepath = $filepath->getPath();

        if( empty($filepath) || !is_string($filepath) )
            return '';

        return $this->cacheManager->getBrowserPath($filepath, 'runtime', ['thumbnail'=>['size'=>$size]], null, UrlGeneratorInterface::ABSOLUTE_PATH);
	}

    public function within(?string $slug, $collection): ?string
    {
        if( is_array($collection) )
            $collection = $collection[0];

        if( $collection )
            return $this->router->generate('sylius_shop_product_in_collection', ['slug' => $slug, 'collection'=>$collection->getSlug()]);
        else
            return $this->router->generate('sylius_shop_product_show', ['slug' => $slug]);
    }

    public function append(?string $text, $suffix): string
    {
        if( !$text )
            return '';

        return $text.$suffix;
    }

    public function prepend(string $text, $prefix): string
    {
        return $prefix.$text;
    }

    public function hmac_sha1(string $text): string
    {
        return sha1($text);
    }

    public function handle($text, string $divider = '-'): string
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
