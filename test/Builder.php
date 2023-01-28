<?php

namespace test\eLife\ApiSdk;

use BadMethodCallException;
use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticlePreprint;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Bioprotocol;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Funder;
use eLife\ApiSdk\Model\Funding;
use eLife\ApiSdk\Model\FundingAward;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\Profile;
use eLife\ApiSdk\Model\PromotionalCollection;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Reviewer;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

final class Builder
{
    private $model;
    private $testData;
    private static $defaults;
    private static $sampleRecipes;

    private function defaultTestData()
    {
        if (null === self::$defaults) {
            self::$defaults = [
                AccessControl::class => function () {
                    return [
                        'value' => 'sample',
                        'access' => AccessControl::ACCESS_PUBLIC,
                    ];
                },
                Address::class => function () {
                    return [
                        'formatted' => new ArraySequence(['foo', 'bar']),
                        'streetAddress' => new EmptySequence(),
                        'locality' => new EmptySequence(),
                        'area' => new EmptySequence(),
                        'country' => null,
                        'postalCode' => null,
                    ];
                },
                Bioprotocol::class => function () {
                    return [
                        'sectionId' => 's1-2-3',
                        'title' => 'Section title',
                        'status' => false,
                        'uri' => 'https://example.com',
                    ];
                },
                BlogArticle::class => function () {
                    return [
                        'id' => '359325',
                        'title' => 'Media coverage: Slime can see',
                        'published' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'updated' => null,
                        'impactStatement' => null,
                        'socialImage' => promise_for(null),
                        'content' => new ArraySequence([
                            new Paragraph('blogArticle 359325 content'),
                        ]),
                        'subjects' => new EmptySequence(),
                    ];
                },
                Collection::class => function () {
                    return [
                        'id' => 'tropical-disease',
                        'title' => 'Tropical disease',
                        'impactStatement' => null,
                        'publishedDate' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'updatedDate' => null,
                        'banner' => promise_for(self::for(Image::class)->sample('banner')),
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                        'socialImage' => promise_for(null),
                        'subjects' => new EmptySequence(),
                        'selectedCurator' => self::dummy(Person::class),
                        'selectedCuratorEtAl' => false,
                        'curators' => new ArraySequence([
                            self::dummy(Person::class),
                        ]),
                        'summary' => new ArraySequence([
                            new Paragraph('collection tropical-disease summary'),
                        ]),
                        'content' => new EmptySequence(),
                        'relatedContent' => new EmptySequence(),
                        'podcastEpisodes' => new EmptySequence(),
                    ];
                },
                PromotionalCollection::class => function () {
                    return [
                        'id' => 'highlights-from-japan',
                        'title' => 'Highlights from Japan',
                        'impactStatement' => null,
                        'publishedDate' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'updatedDate' => null,
                        'banner' => promise_for(self::for(Image::class)->sample('banner')),
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                        'socialImage' => promise_for(null),
                        'subjects' => new EmptySequence(),
                        'editors' => new ArraySequence([
                            self::dummy(Person::class),
                        ]),
                        'summary' => new ArraySequence([
                            new Paragraph('promotional collection highlights-from-japan summary'),
                        ]),
                        'content' => new EmptySequence(),
                        'relatedContent' => new EmptySequence(),
                        'podcastEpisodes' => new EmptySequence(),
                    ];
                },
                DataSet::class => function () {
                    return [
                        'id' => 'id',
                        'date' => new Date(2000),
                        'authors' => [new PersonAuthor(new PersonDetails('preferred name', 'index name'))],
                        'authorsEtAl' => false,
                        'title' => 'title',
                        'dataId' => null,
                        'details' => null,
                        'doi' => null,
                        'uri' => 'http://www.example.com/',
                    ];
                },
                Digest::class => function () {
                    return [
                        'id' => '1',
                        'title' => 'Digest 1 title',
                        'impactStatement' => null,
                        'stage' => 'published',
                        'published' => null,
                        'updated' => null,
                        'thumbnail' => Builder::for(Image::class)->sample('thumbnail'),
                        'socialImage' => null,
                        'subjects' => new EmptySequence(),
                        'content' => new ArraySequence([new Paragraph('Digest 1 text')]),
                        'relatedContent' => new ArraySequence([Builder::dummy(ArticlePoA::class)]),
                    ];
                },
                Event::class => function () {
                    return [
                        'id' => '1',
                        'title' => 'Event 1 title',
                        'impactStatement' => null,
                        'publishedDate' => new DateTimeImmutable('yesterday', new DateTimeZone('Z')),
                        'updatedDate' => null,
                        'starts' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'ends' => new DateTimeImmutable('tomorrow', new DateTimeZone('Z')),
                        'timeZone' => new DateTimeZone('Z'),
                        'content' => new ArraySequence([new Paragraph('Event 1 text')]),
                        'uri' => null,
                        'socialImage' => promise_for(null),
                    ];
                },
                ExternalArticle::class => function () {
                    return [
                        'articleTitle' => 'External article title',
                        'journal' => 'Another journal',
                        'authorLine' => 'Author et al',
                        'uri' => 'http://www.example.com/',
                    ];
                },
                Image::class => function () {
                    return [
                        'uri' => 'https://iiif.elifesciences.org/example.jpg',
                        'altText' => '',
                        'attribution' => new EmptySequence(),
                        'source' => new File(
                            'image/jpeg',
                            'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                            'example.jpg'
                        ),
                        'width' => 1000,
                        'height' => 500,
                        'focalPointX' => 50,
                        'focalPointY' => 50,
                    ];
                },
                Interview::class => function () {
                    return [
                        'id' => '1',
                        'interviewee' => new Interviewee(
                            new PersonDetails('Ramanath Hegde', 'Hegde, Ramanath'),
                            new EmptySequence()
                        ),
                        'title' => 'Controlling traffic',
                        'published' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'updated' => null,
                        'impactStatement' => null,
                        'thumbnail' => null,
                        'socialImage' => promise_for(null),
                        'content' => new ArraySequence([new Paragraph('Interview 1 text')]),
                    ];
                },
                Reviewer::class => function () {
                    return [
                        'person' => new PersonDetails('Josiah Carberry', 'Carberry, Josiah', '0000-0002-1825-0097'),
                        'role' => 'Reviewing editor',
                        'affiliations' => [],
                    ];
                },
                ReviewedPreprint::class => function() {
                    return [
                        'id' => '1',
                        'title' => 'Reviewed preprint',
                        'status' => 'reviewed',
                        'stage' => 'published',
                        'doi' => '10.7554/eLife.19560',
                        'indexContent' => promise_for('Reviewed preprint'),
                        'authorLine' => 'Lee R Berger, John Hawks ... Scott A Williams',
                        'titlePrefix' => 'Title prefix',
                        'published' => new DateTimeImmutable('2022-08-01T00:00:00Z'),
                        'reviewedDate' => new DateTimeImmutable('2022-08-01T00:00:00Z'),
                        'versionDate' => new DateTimeImmutable('2022-08-01T00:00:00Z'),
                        'statusDate' => new DateTimeImmutable('2022-08-01T00:00:00Z'),
                        'volume' => null,
                        'elocationId' => null,
                        'pdf' => null,
                        'subjects' => new EmptySequence(),
                        'curationLabels' => [],
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                    ];
                },
                Subject::class => function () {
                    return [
                        'id' => 'subject1',
                        'name' => 'Subject 1',
                        'impactStatement' => promise_for('Subject subject1 impact statement'),
                        'aimsAndScope' => new EmptySequence(),
                        'banner' => promise_for(self::for(Image::class)->sample('banner')),
                        'thumbnail' => promise_for(self::for(Image::class)->sample('thumbnail')),
                    ];
                },
                Person::class => function () {
                    return [
                        'id' => 'jqpublic',
                        'details' => new PersonDetails('preferred name', 'index name'),
                        'givenNames' => promise_for(null),
                        'surname' => promise_for(null),
                        'type' => 'senior-editor',
                        'typeLabel' => 'Senior Editor',
                        'image' => null,
                        'affiliations' => new EmptySequence(),
                        'research' => promise_for(null),
                        'profile' => new EmptySequence(),
                        'competingInterests' => promise_for(null),
                        'emailAddresses' => new EmptySequence(),
                    ];
                },
                PodcastEpisode::class => function () {
                    return [
                        'number' => 4,
                        'title' => 'September 2013',
                        'impactStatement' => null,
                        'published' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'updated' => null,
                        'banner' => rejection_for('No banner'),
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                        'socialImage' => promise_for(null),
                        'sources' => [
                            new PodcastEpisodeSource(
                                'audio/mpeg',
                                'http://example.com/podcast.mp3'
                            ),
                        ],
                        'chapters' => new PromiseSequence(rejection_for('no chapters')),
                    ];
                },
                PressPackage::class => function () {
                    return [
                        'id' => '1',
                        'title' => 'Press package title',
                        'published' => new DateTimeImmutable('now', new DateTimeZone('Z')),
                        'updated' => null,
                        'impactStatement' => null,
                        'socialImage' => promise_for(null),
                        'subjects' => new EmptySequence(),
                        'content' => new ArraySequence([new Paragraph('Press package 1 text')]),
                        'relatedContent' => new ArraySequence([Builder::dummy(ArticlePoA::class)]),
                        'mediaContacts' => new EmptySequence(),
                        'about' => new EmptySequence(),
                    ];
                },
                Profile::class => function () {
                    return [
                        'id' => 'jqpublic',
                        'details' => new PersonDetails('preferred name', 'index name'),
                        'affiliations' => new EmptySequence(),
                        'emailAddresses' => new EmptySequence(),
                    ];
                },
                ArticlePreprint::class => function () {
                    return [
                        'description' => 'This manuscript was published as a pre-print at bioRxiv.',
                        'uri' => 'https://doi.org/10.1101/2019.08.22',
                        'date' => new DateTimeImmutable('2019-02-15T00:00:00Z'),
                    ];
                },
                ArticlePoA::class => $articlePoA = function () {
                    return [
                        'id' => '14107',
                        'stage' => 'published',
                        'type' => 'research-article',
                        'version' => 1,
                        'doi' => '10.7554/eLife.14107',
                        'authorLine' => 'Yongjian Huang et al',
                        'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                        'titlePrefix' => null,
                        'published' => new DateTimeImmutable('2016-03-28T00:00:00Z'),
                        'versionDate' => new DateTimeImmutable('2016-03-28T00:00:00Z'),
                        'statusDate' => new DateTimeImmutable('2016-03-28T00:00:00Z'),
                        'volume' => 5,
                        'elocationId' => 'e14107',
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                        'socialImage' => self::for(Image::class)->sample('social'),
                        'pdf' => null,
                        'xml' => promise_for('http://www.example.com/xml'),
                        'subjects' => new EmptySequence(),
                        'researchOrganisms' => [],
                        'abstract' => new ArticleSection(new ArraySequence([new Paragraph('Article 14107 abstract text')])),
                        'issue' => promise_for(1),
                        'copyright' => promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                        'authors' => new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]),
                        'reviewers' => new ArraySequence([new Reviewer(new PersonDetails('Reviewer', 'Reviewer'), 'Role')]),
                        'ethics' => new ArraySequence([new Paragraph('ethics')]),
                        'funding' => promise_for(new Funding(
                            new ArraySequence([
                                new FundingAward(
                                    'award',
                                    new Funder(new Place(['Funder']), '10.13039/501100001659'),
                                    'awardId',
                                    new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))])
                                ),
                            ]),
                            'Funding statement'
                        )),
                        'dataAvailability' => new ArraySequence([new Paragraph('Data availability')]),
                        'generatedDataSets' => new ArraySequence([new DataSet('id', Date::fromString('2000-01-02'), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title', 'data id', 'details', '10.1000/182', 'https://doi.org/10.1000/182')]),
                        'usedDataSets' => new ArraySequence([new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/')]),
                        'additionalFiles' => new ArraySequence([new AssetFile(null, 'file1', 'Additional file 1', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'https://placehold.it/900x450', 'image.jpeg'))]),
                    ];
                },
                ArticleVoR::class => function () {
                    return [
                        'id' => '09560',
                        'stage' => 'published',
                        'version' => 1,
                        'type' => 'research-article',
                        'doi' => '10.7554/eLife.09560',
                        'authorLine' => 'Lee R Berger et al',
                        'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                        'titlePrefix' => null,
                        'published' => new DateTimeImmutable('2015-09-10T00:00:00Z'),
                        'versionDate' => new DateTimeImmutable('2015-09-10T00:00:00Z'),
                        'statusDate' => new DateTimeImmutable('2015-09-10T00:00:00Z'),
                        'reviewedDate' => null,
                        'volume' => 4,
                        'elocationId' => 'e09560',
                        'thumbnail' => self::for(Image::class)->sample('thumbnail'),
                        'socialImage' => self::for(Image::class)->sample('social'),
                        'pdf' => null,
                        'figuresPdf' => null,
                        'xml' => promise_for('http://www.example.com/xml'),
                        'subjects' => new EmptySequence(),
                        'curationLabels' => [],
                        'researchOrganisms' => [],
                        'abstract' => new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')])),
                        'issue' => promise_for(1),
                        'copyright' => promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                        'authors' => new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))]),
                        'reviewers' => new ArraySequence([new Reviewer(new PersonDetails('Reviewer', 'Reviewer'), 'Role')]),
                        'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                        'keywords' => new ArraySequence(['Article 09560 keyword']),
                        'digest' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 digest')]), '10.7554/eLife.09560digest')),
                        'content' => new ArraySequence([new Section('Article 09560 section title', 'article09560section', new ArraySequence([new Paragraph('Article 09560 text')]))]),
                        'appendices' => new ArraySequence([
                            new Appendix(
                                'app1',
                                'Appendix 1',
                                new ArraySequence([
                                    new Section(
                                        'Appendix 1 title',
                                        'app1-1',
                                        new ArraySequence([new Paragraph('Appendix 1 text')])
                                    ),
                                ]),
                                '10.7554/eLife.09560.app1'
                            ),
                        ]),
                        'references' => $references = new ArraySequence([
                            new BookReference(
                                'ref1',
                                new Date(2000),
                                null,
                                [
                                    new PersonAuthor(new PersonDetails(
                                        'preferred name',
                                        'index name'
                                    )),
                                ],
                                false,
                                [],
                                false,
                                'book title',
                                new Place(['publisher'])
                            ),
                        ]),
                        'additionalFiles' => new ArraySequence([new AssetFile(null, 'file1', 'Additional file 1', null, new EmptySequence(), new EmptySequence(), new File('image/jpeg', 'https://placehold.it/900x450', 'image.jpeg'))]),
                        'dataAvailability' => new ArraySequence([new Paragraph('Data availability')]),
                        'generatedDataSets' => new ArraySequence([new DataSet('id', Date::fromString('2000-01-02'), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title', 'data id', 'details', '10.1000/182', 'https://doi.org/10.1000/182')]),
                        'usedDataSets' => new ArraySequence([new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/')]),
                        'acknowledgements' => new ArraySequence([new Paragraph('acknowledgements')]),
                        'ethics' => new ArraySequence([new Paragraph('ethics')]),
                        'funding' => promise_for(new Funding(
                            new ArraySequence([
                                new FundingAward(
                                    'award',
                                    new Funder(new Place(['Funder']), '10.13039/501100001659'),
                                    'awardId',
                                    new ArraySequence([new PersonAuthor(new PersonDetails('Author', 'Author'))])
                                ),
                            ]),
                            'Funding statement'
                        )),
                        'editorEvaluation' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Editor evaluation')]))),
                        'editorEvaluationScietyUri' => promise_for('https://editor-evaluation.com'),
                        'decisionLetter' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Decision letter')]))),
                        'decisionLetterDescription' => new ArraySequence([new Paragraph('Decision letter description')]),
                        'authorResponse' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Author response')]))),
                        'curationLabels' => [],
                        'reviewedDate' => null,
                        'elifeAssessment' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('eLife Assessment')]))),
                        'elifeAssessmentTitle' => promise_for('eLife assessment'),
                        'elifeAssessmentScietyUri' => promise_for('https://elife-assessment.com'),
                        'recommendationsForAuthors' => promise_for(new ArticleSection(new ArraySequence([new Paragraph('Recommendations For Authors')]))),
                        'recommendationsForAuthorsTitle' => promise_for('Recommendations for authors'),
                    ];
                },
            ];
        }

        return self::$defaults;
    }

    private function sampleRecipes()
    {
        if (null === self::$sampleRecipes) {
            self::$sampleRecipes = [
                Address::class => [
                    'simple' => function ($builder) {
                        return $builder
                            ->withSequenceOfFormatted('address')
                            ->withSequenceOfStreetAddress('street address')
                            ->withSequenceOfLocality('locality')
                            ->withSequenceOfArea('area')
                            ->withCountry('country')
                            ->withPostalCode('postal code');
                    },
                    'somewhere' => function ($builder) {
                        return Builder::for(Address::class)
                            ->withSequenceOfFormatted('somewhere')
                            ->withSequenceOfLocality('somewhere');
                    },
                ],
                Image::class => [
                    'banner' => function () {
                        return new Image('', 'https://iiif.elifesciences.org/banner.jpg', new EmptySequence(), new File('image/jpeg', 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg', 'banner.jpg'), 1800, 900, 50, 50);
                    },
                    'thumbnail' => function () {
                        return new Image('', 'https://iiif.elifesciences.org/thumbnail.jpg', new EmptySequence(), new File('image/jpeg', 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg', 'thumbnail.jpg'), 140, 140, 50, 50);
                    },
                    'social' => function () {
                        return new Image('', 'https://iiif.elifesciences.org/social.jpg', new EmptySequence(), new File('image/jpeg', 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg', 'social.jpg'), 600, 600, 50, 50);
                    },
                ],
                ArticlePreprint::class => [
                    '1' => function ($builder) {
                        return $builder
                            ->withDescription('Article preprint 1')
                            ->withUri('https://doi.org/10.1101/2019.08.22')
                            ->withDate(new DateTimeImmutable('2019-02-15T00:00:00Z'));
                    },
                ],
                ArticlePoA::class => [
                    'growth-factor' => function ($builder) {
                        return $builder
                            ->withId('14107')
                            ->withStage('published')
                            ->withVersion(1)
                            ->withDoi('10.7554/eLife.14107')
                            ->withAuthorLine('Yongjian Huang et al')
                            ->withTitle('Molecular basis for multimerization in the activation of the epidermal growth factor')
                            ->withPublished(new DateTimeImmutable('2016-03-28T00:00:00Z'))
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withSocialImage(self::for(Image::class)->sample('social'))
                            ->withVersionDate(new DateTimeImmutable('2016-03-28T00:00:00Z'))
                            ->withStatusDate(new DateTimeImmutable('2016-03-28T00:00:00Z'))
                            ->withVolume(5)
                            ->withElocationId('e14107')
                            ->withSubjects(new EmptySequence());
                    },
                    '1' => function ($builder) {
                        return $builder
                            ->withId('1')
                            ->withStage('published')
                            ->withVersion(1)
                            ->withDoi('10.7554/eLife.1')
                            ->withAuthorLine('Author et al')
                            ->withTitle('Article 1 title')
                            ->withTitlePrefix('Article 1 title prefix')
                            ->withPublished(new DateTimeImmutable('2000-01-01T00:00:00Z'))
                            ->withVersionDate(new DateTimeImmutable('1999-12-31T00:00:00Z'))
                            ->withStatusDate(new DateTimeImmutable('1999-12-31T00:00:00Z'))
                            ->withVolume(1)
                            ->withElocationId('e1')
                            ->withPdf('http://www.example.com/pdf')
                            ->withPromiseOfXml('http://www.example.com/xml')
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('1'),
                            ]))
                            ->withResearchOrganisms([
                                'Article 1 research organism',
                            ])
                            ->withAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 1 abstract text')])));
                    },
                ],
                ArticleVoR::class => [
                    'homo-naledi' => function ($builder) {
                        return $builder
                            ->withId('09560')
                            ->withStage('published')
                            ->withVersion(1)
                            ->withDoi('10.7554/eLife.09560')
                            ->withAuthorLine('Lee R Berger et al')
                            ->withTitle('<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa')
                            ->withPublished(new DateTimeImmutable('2015-09-10T00:00:00Z'))
                            ->withVersionDate(new DateTimeImmutable('2015-09-10T00:00:00Z'))
                            ->withStatusDate(new DateTimeImmutable('2015-09-10T00:00:00Z'))
                            ->withVolume(4)
                            ->withElocationId('e09560')
                            ->withPdf('https://elifesciences.org/content/4/e09560.pdf')
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('genomics-evolutionary-biology'),
                            ]))
                            ->withAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')]), '10.7554/eLife.09560abstract'))
                            ->withImpactStatement('A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.')
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withSocialImage(self::for(Image::class)->sample('social'))
                            ->withContent(new ArraySequence([new Section('Article 09560 section title', 'article09560section', new ArraySequence([new Paragraph('Article 09560 text')]))]))
                            ->withContent(new ArraySequence([new Section('Article 09560 section title', 'article09560section', new ArraySequence([new Paragraph('Article 09560 text')]))]))
                            ->withAcknowledgements(new ArraySequence([new Paragraph('acknowledgements')]))
                            ->withEthics(new ArraySequence([new Paragraph('ethics')]))
                            ->withEditorEvaluation(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 editor evaluation text')]), '10.7554/eLife.09560editorEvaluation', 'editor-evaluation-id')))
                            ->withEditorEvaluationScietyUri(promise_for('https://editor-evaluation-09560.com'))
                            ->withDecisionLetter(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 decision letter text')]), '10.7554/eLife.09560decisionLetter', 'decision-letter-id')))
                            ->withDecisionLetterDescription(new ArraySequence([new Paragraph('Article 09560 decision letter description')]))
                            ->withAuthorResponse(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 author response text')]), '10.7554/eLife.09560authorResponse', 'author-response-id')))
                            ->withElifeAssessment(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 elife assessment text')]), '10.7554/eLife.09560elifeAssessment', 'elife-assessment-id')))
                            ->withElifeAssessmentTitle(promise_for('eLife assessment'))
                            ->withElifeAssessmentScietyUri(promise_for('https://elife-assessment-09560.com'))
                            ->withRecommendationsForAuthors(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 recommendations for authors text')]), '10.7554/eLife.09560recommendationsForAuthors', 'recommendations-for-authors-id')))
                            ->withRecommendationsForAuthorsTitle(promise_for('Recommendations for authors'));
                    },
                ],
                BlogArticle::class => [
                    'slime' => function ($builder) {
                        return $builder
                            ->withId(359325)
                            ->withTitle('Media coverage: Slime can see')
                            ->withImpactStatement('In their research paper – Cyanobacteria use micro-optics to sense light direction – Schuergers et al. reveal how bacterial cells act as the equivalent of a microscopic eyeball or the world’s oldest and smallest camera eye, allowing them to ‘see’.')
                            ->withPublished(new DateTimeImmutable('2016-07-08T08:33:25Z'))
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('biophysics-structural-biology'),
                            ]))
                            ->withContent(new ArraySequence([
                                new Paragraph('Blog article 359325 text'),
                            ]));
                    },
                ],
                Collection::class => [
                    'tropical-disease' => function ($builder) {
                        return $builder
                            ->withId('tropical-disease')
                            ->withTitle('Tropical disease')
                            ->withPublishedDate(new DateTimeImmutable('2000-01-01T00:00:00Z'))
                            ->withThumbnail(Builder::for(Image::class)->sample('thumbnail'))
                            ->withSelectedCurator($pjha = Builder::for(Person::class)->sample('pjha'))
                            ->withCurators(new ArraySequence([
                                Builder::for(Person::class)->sample('bcooper'),
                                $pjha,
                            ]))
                            ->withSummary(new EmptySequence())
                            ->withContent(new ArraySequence([
                                Builder::for(BlogArticle::class)
                                    ->sample('slime'),
                            ]));
                    },
                ],
                PromotionalCollection::class => [
                    'highlights-from-japan' => function ($builder) {
                        return $builder
                            ->withId('highlights-from-japan')
                            ->withTitle('Highlights from Japan')
                            ->withPublishedDate(new DateTimeImmutable('2000-01-01T00:00:00Z'))
                            ->withThumbnail(Builder::for(Image::class)->sample('thumbnail'))
                            ->withEditors(new ArraySequence([
                                Builder::for(Person::class)->sample('bcooper'),
                                Builder::for(Person::class)->sample('pjha'),
                            ]))
                            ->withSummary(new EmptySequence())
                            ->withContent(new ArraySequence([
                                Builder::for(BlogArticle::class)
                                    ->sample('slime'),
                            ]));
                    },
                ],
                Digest::class => [
                    'neighbourhood-watch' => function ($builder) {
                        return $builder
                            ->withId('2')
                            ->withTitle('Neighborhood watch')
                            ->withImpactStatement('Roundworms modify the chemical signals they produce to tell others whether they’re in a good or bad environment.')
                            ->withStage('published')
                            ->withPublished(new DateTimeImmutable('2018-07-06T09:06:01Z'))
                            ->withUpdated(new DateTimeImmutable('2018-07-06T16:23:24Z'))
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withSocialImage(self::for(Image::class)->sample('social'))
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('biophysics-structural-biology'),
                            ]))
                            ->withContent(new ArraySequence([new Paragraph('Digest 2 text')]))
                            ->withRelatedContent(new ArraySequence([
                                Builder::for(ArticleVoR::class)
                                    ->sample('homo-naledi'),
                            ]));
                    },
                ],
                Event::class => [
                    'changing-peer-review' => function ($builder) {
                        return $builder
                            ->withId('event1')
                            ->withTitle('Changing peer review in cancer research: a seminar at Fred Hutch')
                            ->withImpactStatement('How eLife is influencing the culture of peer review')
                            ->withPromiseOfSocialImage(Builder::for(Image::class)->sample('social'))
                            ->withPublishedDate(new DateTimeImmutable('2016-08-01T00:00:00Z'))
                            ->withUpdatedDate(new DateTimeImmutable('2016-08-02T00:00:00Z'))
                            ->withStarts(new DateTimeImmutable('2016-04-22T20:00:00Z'))
                            ->withEnds(new DateTimeImmutable('2016-04-22T21:00:00Z'))
                            ->withTimeZone(new DateTimeZone('America/Los_Angeles'))
                            ->withContent(new EmptySequence())
                            ->withUri('https://crm.elifesciences.org/crm/civicrm/event/info?reset=1&id=27');
                    },
                ],
                Interview::class => [
                    'controlling-traffic' => function ($builder) {
                        return $builder
                            ->withId('1')
                            ->withTitle('Controlling traffic')
                            ->withInterviewee(new Interviewee(
                                new PersonDetails('Ramanath Hegde', 'Hegde, Ramanath'),
                                new ArraySequence([
                                    new IntervieweeCvLine('date', 'text'),
                                ])
                            ))
                            ->withImpactStatement('Ramanath Hegde is a Postdoctoral Fellow at the Institute of Protein Biochemistry in Naples, Italy, where he investigates ways of preventing cells from destroying mutant proteins.')
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withPromiseOfSocialImage(self::for(Image::class)->sample('social'))
                            ->withPublished(new DateTimeImmutable('2016-01-29T16:22:28Z'))
                            ->withContent(new ArraySequence([new Paragraph('Interview 1 text')]));
                    },
                ],
                Person::class => [
                    'bcooper' => function ($builder, $context) {
                        $person = $builder
                            ->withId('bcooper')
                            ->withType('reviewing-editor')
                            ->withTypeLabel('Reviewing Editor')
                            ->withDetails(new PersonDetails(
                                'Ben Cooper',
                                'Cooper, Ben'
                            ));

                        if (!$context['snippet']) {
                            $person
                                ->withPromiseOfResearch('')
                                ->withProfile(new EmptySequence())
                                ->withPromiseOfCompetingInterests('');
                        }

                        return $person;
                    },
                    'pjha' => function ($builder, $context) {
                        $person = $builder
                            ->withId('pjha')
                            ->withType('senior-editor')
                            ->withTypeLabel('Senior Editor')
                            ->withDetails(new PersonDetails(
                                'Prabhat Jha',
                                'Jha, Prabhat'
                            ));
                        if (!$context['snippet']) {
                            $person
                                ->withPromiseOfResearch('')
                                ->withProfile(new EmptySequence())
                                ->withPromiseOfCompetingInterests('');
                        }

                        return $person;
                    },
                ],
                PodcastEpisode::class => [
                    '29' => function ($builder) {
                        return $builder
                            ->withNumber(29)
                            ->withTitle('April/May 2016')
                            ->withPublished(new DateTimeImmutable('2016-05-27T13:19:42Z'))
                            ->withPromiseOfBanner(self::for(Image::class)->sample('banner'))
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'))
                            ->withPromiseOfSocialImage(self::for(Image::class)->sample('social'))
                            ->withSources([
                                new PodcastEpisodeSource(
                                    'audio/mpeg',
                                    'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.05.mp3'
                                ),
                            ])
                            ->withChapters(new ArraySequence([
                                new PodcastEpisodeChapter(1, 'Chapter title', 'Long chapter title', 0, 'Chapter impact statement', new ArraySequence([
                                    self::for(ArticlePoA::class)->sample('1'),
                                ])),
                            ]));
                    },
                ],
                ReviewedPreprint::class => [
                    'minimum' => function($builder) {
                        return $builder
                            ->withId('1')
                            ->withTitle('title')
                            ->withStage('published')
                            ->withStatus('reviewed')
                            ->withDoi(null)
                            ->withPromiseOfIndexContent(null)
                            ->withAuthorLine(null)
                            ->withPublished(null)
                            ->withReviewedDate(null)
                            ->withVersionDate(null)
                            ->withTitlePrefix(null)
                            ->withStatusDate(null)
                            ->withVolume(null)
                            ->withElocationId(null)
                            ->withPdf(null)
                            ->withSubjects(new EmptySequence())
                            ->withCurationLabels([])
                            ->withThumbnail(null);
                    },
                    'complete' => function($builder) {
                        return $builder
                            ->withId('1')
                            ->withTitle('title')
                            ->withStage('published')
                            ->withStatus('reviewed')
                            ->withDoi('doi')
                            ->withTitlePrefix('title prefix')
                            ->withPromiseOfIndexContent('indexContent')
                            ->withAuthorLine('authorLine')
                            ->withPublished(new DateTimeImmutable('2016-09-16T12:34:56Z'))
                            ->withReviewedDate(new DateTimeImmutable('2016-09-16T12:34:56Z'))
                            ->withVersionDate(new DateTimeImmutable('2016-09-16T12:34:56Z'))
                            ->withStatusDate(new DateTimeImmutable('2016-09-16T12:34:56Z'))
                            ->withVolume(4)
                            ->withElocationId('elocationId')
                            ->withPdf('pdf')
                            ->withSubjects(new ArraySequence([
                                self::for(Subject::class)->sample('biophysics-structural-biology'),
                            ]))
                            ->withCurationLabels(['curation-label'])
                            ->withThumbnail(self::for(Image::class)->sample('thumbnail'));
                    }
                ],
                Subject::class => [
                    '1' => function ($builder) {
                        return $builder
                            ->withId('1')
                            ->withName('Subject 1 name')
                            ->withPromiseOfImpactStatement('Subject 1 impact statement')
                            ->withAimsAndScope(new ArraySequence([new Paragraph('Subject 1 aims and scope')]));
                    },
                    'genomics-evolutionary-biology' => function ($builder) {
                        return $builder
                            ->withId('genomics-evolutionary-biology')
                            ->withName('Genomics and Evolutionary Biology')
                            ->withPromiseOfImpactStatement('Subject genomics-evolutionary-biology impact statement')
                            ->withAimsAndScope(new ArraySequence([new Paragraph('Subject genomics-evolutionary-biology aims and scope')]));
                    },
                    'biophysics-structural-biology' => function ($builder) {
                        return $builder
                            ->withId('biophysics-structural-biology')
                            ->withName('Biophysics and Structural Biology')
                            ->withPromiseOfImpactStatement('Subject biophysics-structural-biology impact statement')
                            ->withAimsAndScope(new ArraySequence([new Paragraph('Subject biophysics-structural-biology aims and scope')]));
                    },
                    'epidemiology-global-health' => function ($builder) {
                        return $builder
                            ->withId('epidemiology-global-health')
                            ->withName('Epidemiology and Global Health')
                            ->withPromiseOfImpactStatement('Subject epidemiology-global-health impact statement')
                            ->withAimsAndScope(new ArraySequence([new Paragraph('Subject epidemiology-global-health aims and scope')]));
                    },
                    'microbiology-infectious-disease' => function ($builder) {
                        return $builder
                            ->withId('microbiology-infectious-disease')
                            ->withName('Microbiology and Infectious Disease')
                            ->withPromiseOfImpactStatement('Subject microbiology-infectious-disease impact statement')
                            ->withAimsAndScope(new EmptySequence());
                    },
                ],
            ];
        }

        return self::$sampleRecipes;
    }

    public static function for($model) : self
    {
        return (new self())->create($model);
    }

    /**
     * @return object instance of $model
     */
    public static function dummy($model)
    {
        return self::for($model)->__invoke();
    }

    public function create($model) : self
    {
        $this->model = $model;
        $defaults = $this->defaultTestData($model);
        if (!array_key_exists($model, $defaults)) {
            throw new InvalidArgumentException("No defaults available for $model");
        }

        $this->testData = call_user_func($defaults[$model]);

        return $this;
    }

    /**
     * @method with...($value)  e.g. withImpactStatement('a string')
     * @method withPromiseOf...($value)  e.g. withPromiseOfBanner(new Image(...))
     */
    public function __call($name, $args) : self
    {
        if (preg_match('/^withPromiseOf(.*)$/', $name, $matches)) {
            $field = lcfirst($matches[1]);
            $this->ensureExistingField($field);
            $this->ensureSingleArgument($args);
            $this->testData[$field] = promise_for($args[0]);
        } elseif (preg_match('/^withSequenceOf(.*)$/', $name, $matches)) {
            $field = lcfirst($matches[1]);
            $this->ensureExistingField($field);
            $this->testData[$field] = new ArraySequence($args);
        } elseif (preg_match('/^with(.*)$/', $name, $matches)) {
            $field = lcfirst($matches[1]);
            $this->ensureExistingField($field);
            $this->ensureSingleArgument($args);
            $this->testData[$field] = $args[0];
        } else {
            throw new BadMethodCallException("Magic method $name is not supported by the Builder class");
        }

        return $this;
    }

    /**
     * @return object instance of $this->model
     */
    public function __invoke()
    {
        $class = new ReflectionClass($this->model);
        $constructorArgumentNames = array_map(function ($p) {
            return $p->getName();
        }, $class->getConstructor()->getParameters());
        $constructorArguments = [];
        $testDataRemainingToUse = $this->testData;
        if ($unused = array_diff($constructorArgumentNames, array_keys($testDataRemainingToUse))) {
            throw new LogicException("Some defaults were not specified, but expected by the constructor of $this->model: ".var_export(array_values($unused), true));
        }
        foreach ($constructorArgumentNames as $name) {
            $constructorArguments[] = $testDataRemainingToUse[$name];
            unset($testDataRemainingToUse[$name]);
        }
        if ($testDataRemainingToUse) {
            throw new LogicException("Some defaults were specified, but not used by the constructor of $this->model: ".var_export($testDataRemainingToUse, true));
        }
        $instance = $class->newInstanceArgs($constructorArguments);

        return $instance;
    }

    /**
     * @return object instance of $this->model
     */
    public function sample($sampleName, $context = [])
    {
        $samples = $this->sampleRecipes();

        if (!array_key_exists($sampleName, $samples[$this->model])) {
            throw new InvalidArgumentException("Sample $sampleName not found for {$this->model}");
        }
        if (!array_key_exists('snippet', $context)) {
            $context['snippet'] = false;
        }
        $sample = call_user_func(
            $samples[$this->model][$sampleName],
            $this,
            $context
        );
        if ($sample instanceof self) {
            return $sample();
        } else {
            return $sample;
        }
    }

    private function ensureExistingField($field)
    {
        $allowedFields = array_keys($this->testData);
        if (!in_array($field, $allowedFields)) {
            throw new BadMethodCallException("Field $field is not allowed for {$this->model}. Allowed fields: ".implode(', ', $allowedFields));
        }
    }

    private function ensureSingleArgument($args)
    {
        if (count($args) > 1) {
            throw new BadMethodCallException('Too many arguments: '.var_export($args, true));
        }
    }

    private function rejectSequence()
    {
        return new PromiseSequence(rejection_for('rejecting this sequence'));
    }
}
