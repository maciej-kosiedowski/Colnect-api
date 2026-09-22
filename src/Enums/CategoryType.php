<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Enums;

/**
 * Colnect's collectable categories.
 *
 * The values match the module names used in Colnect URLs and expected by the
 * "cat" filter of every category specific action. The authoritative list can
 * always be fetched at runtime with the categories action.
 */
enum CategoryType: string
{
    case Stamps = 'stamps';
    case Coins = 'coins';
    case BankCards = 'bank_cards';
    case Banknotes = 'banknotes';
    case BeerCoasters = 'beer_coasters';
    case Bottlecaps = 'bottlecaps';
    case CasinoCards = 'casino_cards';
    case DrinkLabels = 'drink_labels';
    case FunctionalCards = 'functional_cards';
    case GiftCards = 'gift_cards';
    case HotelKeyCards = 'hotel_key_cards';
    case KidsMealToys = 'kids_meal_toys';
    case LegoSets = 'lego_sets';
    case LotteryTickets = 'lottery_tickets';
    case Magazines = 'magazines';
    case Medals = 'medals';
    case MusicRecords = 'music_records';
    case MilkCupLids = 'milk_cup_lids';
    case PaperClips = 'paper_clips';
    case PaperNapkins = 'paper_napkins';
    case Phonecards = 'phonecards';
    case PocketCalendars = 'pocket_calendars';
    case Postcards = 'postcards';
    case SportsCards = 'sports_cards';
    case Stickers = 'stickers';
    case SugarPackets = 'sugar_packets';
    case TeaLabels = 'tea_labels';
    case Teabags = 'teabags';
    case Tokens = 'tokens';
    case TradingCardGames = 'trading_card_games';
    case TransportationTickets = 'transportation_tickets';
    case VideoGames = 'video_games';
    case AdmissionTickets = 'admission_tickets';
    case Bookmarks = 'bookmarks';
    case HolyCards = 'holy_cards';
    case DieCastModels = 'die_cast_models';
    case PhilatelicProducts = 'philatelic_products';
    case CigarettePacks = 'cigarette_packs';
    case Comics = 'comics';
    case AircraftSafetyCards = 'aircraft_safety_cards';
    case FruitStickers = 'fruit_stickers';
    case FoodLabels = 'food_labels';
    case ChocolateWrappers = 'chocolate_wrappers';
    case LapelPins = 'lapel_pins';
    case CigarBands = 'cigar_bands';
    case KpopMerch = 'kpop_merch';

    /**
     * Name of the filter used for the producer of this category.
     *
     * CAPI accepts the generic "producer" filter everywhere, but Colnect URLs
     * use a per-category name such as "company" or "brand". Categories without
     * a dedicated name fall back to "producer".
     *
     * @return string
     */
    public function producerFilter(): string
    {
        return match ($this) {
            self::Phonecards, self::Bottlecaps, self::TransportationTickets, self::GiftCards,
            self::BeerCoasters, self::Stickers, self::DrinkLabels, self::MilkCupLids,
            self::FoodLabels, self::ChocolateWrappers => 'company',
            self::Stamps, self::Coins, self::Banknotes, self::Postcards, self::Magazines,
            self::SugarPackets, self::Tokens, self::PocketCalendars, self::Medals,
            self::PaperNapkins, self::PaperClips, self::AdmissionTickets, self::Bookmarks,
            self::HolyCards, self::DieCastModels, self::PhilatelicProducts, self::CigarettePacks,
            self::Comics, self::FruitStickers, self::LapelPins, self::CigarBands => 'country',
            self::Teabags, self::TeaLabels => 'brand',
            self::HotelKeyCards, self::CasinoCards => 'chain',
            self::BankCards => 'provider',
            self::TradingCardGames => 'game',
            self::LegoSets => 'product_line',
            self::FunctionalCards => 'industry',
            self::VideoGames => 'console',
            self::LotteryTickets => 'organization',
            self::SportsCards => 'sport',
            self::KidsMealToys => 'group',
            self::AircraftSafetyCards => 'airline',
            self::KpopMerch => 'release',
            self::MusicRecords => 'producer',
        };
    }

    /**
     * Whether the producer of this category is the issuing country.
     *
     * When true there is no producers list to fetch and the country filter
     * must be sent as "producer" - see the producer_is_country action.
     *
     * @return bool
     */
    public function producerIsCountry(): bool
    {
        return $this->producerFilter() === 'country';
    }
}
