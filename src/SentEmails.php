<?php

namespace Zenstruck\Mailer\Test;

use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SentEmails implements \IteratorAggregate, \Countable
{
    /** @var TestEmail[] */
    private array $emails;

    /**
     * @internal
     */
    public function __construct(TestEmail ...$emails)
    {
        $this->emails = $emails;
    }

    public static function fromEvents(MessageEvents $events): self
    {
        $usingQueue = false;
        $events = $events->getEvents();

        foreach ($events as $event) {
            if ($event->isQueued()) {
                $usingQueue = true;

                break;
            }
        }

        if ($usingQueue) {
            // if using queue, remove non queued messages to avoid duplicates
            $events = \array_filter($events, static fn(MessageEvent $event) => $event->isQueued());
        }

        // convert events to messages
        $messages = \array_map(static fn(MessageEvent $event) => $event->getMessage(), $events);

        // remove non Email messages
        $messages = \array_filter($messages, static fn(RawMessage $message) => $message instanceof Email);

        return new self(...\array_map(static fn(Email $email) => new TestEmail($email), $messages));
    }

    public function first(): TestEmail
    {
        return $this->all()[\array_key_first($this->all())];
    }

    public function last(): TestEmail
    {
        return $this->all()[\array_key_last($this->all())];
    }

    /**
     * @param callable(TestEmail):bool $filter
     */
    public function filter(callable $filter): self
    {
    }

    /**
     * @return TestEmail[]
     */
    public function all(): array
    {
        return $this->emails;
    }

    /**
     * @return \Traversable|TestEmail[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->emails);
    }

    public function count(): int
    {
        return \count($this->emails);
    }
}
