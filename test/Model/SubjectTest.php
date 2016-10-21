<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class SubjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            rejection_for('No banner'), rejection_for('Image should not be unwrapped'));

        $this->assertSame('id', $subject->getId());
    }

    /**
     * @test
     */
    public function it_has_a_name()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            rejection_for('No banner'), rejection_for('Image should not be unwrapped'));

        $this->assertSame('name', $subject->getName());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new Subject('id', 'name', promise_for('impact statement'), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));
        $withOut = new Subject('id', 'name', promise_for(null), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            promise_for($image = new Image('', [900 => 'https://placehold.it/900x450'])),
            rejection_for('No thumbnail'));

        $this->assertEquals($image, $subject->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            rejection_for('No banner'), promise_for($image = new Image('', [900 => 'https://placehold.it/900x450'])));

        $this->assertEquals($image, $subject->getThumbnail());
    }
}
