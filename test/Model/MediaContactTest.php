<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use PHPUnit_Framework_TestCase;

final class MediaContactTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_person_details()
    {
        $mediaContact = new MediaContact($details = new PersonDetails('Person', 'Person'));

        $this->assertEquals($details, $mediaContact->getDetails());
    }

    /**
     * @test
     */
    public function it_may_have_affiliations()
    {
        $with = new MediaContact(new PersonDetails('Person', 'Person'), $affiliations = [new Place(null, null, ['Somewhere'])]);
        $withOut = new MediaContact(new PersonDetails('Person', 'Person'));

        $this->assertEquals($affiliations, $with->getAffiliations());
        $this->assertEmpty($withOut->getAffiliations());
    }

    /**
     * @test
     */
    public function it_may_have_email_addresses()
    {
        $with = new MediaContact(new PersonDetails('Person', 'Person'), [], $emailAddresses = ['foo@example.com']);
        $withOut = new MediaContact(new PersonDetails('Person', 'Person'));

        $this->assertSame($emailAddresses, $with->getEmailAddresses());
        $this->assertEmpty($withOut->getEmailAddresses());
    }

    /**
     * @test
     */
    public function it_may_have_phone_numbers()
    {
        $with = new MediaContact(new PersonDetails('Person', 'Person'), [], [], $phoneNumbers = ['+447700900415']);
        $withOut = new MediaContact(new PersonDetails('Person', 'Person'));

        $this->assertSame($phoneNumbers, $with->getPhoneNumbers());
        $this->assertEmpty($withOut->getPhoneNumbers());
    }
}
