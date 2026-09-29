<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Slimad\ColnectApi\Enums\CategoryType;

class CategoryTypeTest extends TestCase
{
    public function test_every_value_is_a_valid_colnect_module_name(): void
    {
        foreach (CategoryType::cases() as $case) {
            $this->assertMatchesRegularExpression('/^[a-z_]+$/D', $case->value);
        }
    }

    public function test_values_are_unique(): void
    {
        $values = array_map(static fn (CategoryType $case): string => $case->value, CategoryType::cases());

        $this->assertSame($values, array_values(array_unique($values)));
    }

    /**
     * @return array<string, array{CategoryType, string}>
     */
    public static function producerFilterProvider(): array
    {
        return [
            'stamps use the country' => [CategoryType::Stamps, 'country'],
            'coins use the country' => [CategoryType::Coins, 'country'],
            'phonecards use the company' => [CategoryType::Phonecards, 'company'],
            'teabags use the brand' => [CategoryType::Teabags, 'brand'],
            'hotel key cards use the chain' => [CategoryType::HotelKeyCards, 'chain'],
            'bank cards use the provider' => [CategoryType::BankCards, 'provider'],
            'trading card games use the game' => [CategoryType::TradingCardGames, 'game'],
            'lego sets use the product line' => [CategoryType::LegoSets, 'product_line'],
            'functional cards use the industry' => [CategoryType::FunctionalCards, 'industry'],
            'video games use the console' => [CategoryType::VideoGames, 'console'],
            'lottery tickets use the organization' => [CategoryType::LotteryTickets, 'organization'],
            'sports cards use the sport' => [CategoryType::SportsCards, 'sport'],
            'kids meal toys use the group' => [CategoryType::KidsMealToys, 'group'],
            'aircraft safety cards use the airline' => [CategoryType::AircraftSafetyCards, 'airline'],
            'kpop merch uses the release' => [CategoryType::KpopMerch, 'release'],
            'music records fall back to the producer' => [CategoryType::MusicRecords, 'producer'],
        ];
    }

    /**
     * @dataProvider producerFilterProvider
     */
    public function test_it_maps_the_producer_filter(CategoryType $category, string $filter): void
    {
        $this->assertSame($filter, $category->producerFilter());
    }

    public function test_every_category_has_a_producer_filter(): void
    {
        foreach (CategoryType::cases() as $case) {
            $this->assertMatchesRegularExpression('/^[a-z_]+$/D', $case->producerFilter());
        }
    }

    public function test_it_knows_when_the_producer_is_the_country(): void
    {
        $this->assertTrue(CategoryType::Stamps->producerIsCountry());
        $this->assertFalse(CategoryType::Phonecards->producerIsCountry());
    }
}
