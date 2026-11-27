<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Alcaeus\MongoDbAdapter\Helper;

/**
 * alcaeus/mongo-php-adapter 1.2.5, `lib/Alcaeus/MongoDbAdapter/Helper/ReadPreference.php`, with flat's change (Q53).
 *
 * The adapter is archived (its 1.x branch ends in 2023) and calls what ext-mongodb 1.20 deprecates: the integer
 * `ReadPreference::RP_*` constants, an integer mode in the constructor and `ReadPreference::getMode()`. Every
 * database object triggered it. This copy passes the string modes the extension has always accepted (they equal the
 * legacy `\MongoClient::RP_*` values) and reads the mode back through `bsonSerialize()`, which works on every
 * extension version. Nothing else differs; each change is marked `flat (Q53)`.
 *
 * flat's composer.json maps this namespace to this folder, ahead of the adapter's own, so the adapter's copy of
 * this trait is never loaded. See docs/modernization/php-7.4/notes/VENDORED.md.
 *
 * @internal
 */
trait ReadPreference
{
    /**
     * @var \MongoDB\Driver\ReadPreference
     */
    protected $readPreference;

    /**
     * @param string $readPreference
     * @param array $tags
     * @return bool
     */
    abstract public function setReadPreference($readPreference, $tags = null);

    /**
     * @return array
     */
    public function getReadPreference()
    {
        if ($this->readPreference === null) {
            // flat (Q53): the string mode, not the deprecated integer constant
            $this->readPreference = new \MongoDB\Driver\ReadPreference(\MongoClient::RP_PRIMARY);
        }

        // flat (Q53): the mode as a string, not the deprecated getMode()
        $mode = $this->getReadPreferenceModeString();

        switch ($mode) {
            case \MongoClient::RP_PRIMARY_PREFERRED:
                $type = \MongoClient::RP_PRIMARY_PREFERRED;
                break;
            case \MongoClient::RP_SECONDARY:
                $type = \MongoClient::RP_SECONDARY;
                break;
            case \MongoClient::RP_SECONDARY_PREFERRED:
                $type = \MongoClient::RP_SECONDARY_PREFERRED;
                break;
            case \MongoClient::RP_NEAREST:
                $type = \MongoClient::RP_NEAREST;
                break;
            default:
                $type = \MongoClient::RP_PRIMARY;
        }

        $readPreference = ['type' => $type];
        if ($this->readPreference->getTagSets() !== null && $this->readPreference->getTagSets() !== []) {
            $readPreference['tagsets'] = $this->readPreference->getTagSets();
        }

        return $readPreference;
    }

    /**
     * @return bool
     */
    protected function getSlaveOkayFromReadPreference()
    {
        // flat (Q53): the mode as a string, not the deprecated getMode()
        return $this->getReadPreferenceModeString() !== \MongoClient::RP_PRIMARY;
    }

    /**
     * flat (Q53): added. The mode of the current read preference as a string (`primary`, `primaryPreferred`,
     * `secondary`, `secondaryPreferred` or `nearest`), read through `bsonSerialize()` because `getMode()` is
     * deprecated from ext-mongodb 1.20 and `getModeString()` is missing from older extensions.
     *
     * @return string
     */
    protected function getReadPreferenceModeString()
    {
        return ((array) $this->readPreference->bsonSerialize())['mode'];
    }

    /**
     * @param string $readPreference
     * @param array $tags
     * @return bool
     */
    protected function setReadPreferenceFromParameters($readPreference, $tags = null)
    {
        // @internal Passing an array for $readPreference is necessary to avoid conversion voodoo
        // It should not be used externally!
        if (is_array($readPreference)) {
            return $this->setReadPreferenceFromArray($readPreference);
        }

        switch ($readPreference) {
            // flat (Q53): the string modes, not the deprecated integer constants
            case \MongoClient::RP_PRIMARY:
                $mode = \MongoClient::RP_PRIMARY;
                break;
            case \MongoClient::RP_PRIMARY_PREFERRED:
                $mode = \MongoClient::RP_PRIMARY_PREFERRED;
                break;
            case \MongoClient::RP_SECONDARY:
                $mode = \MongoClient::RP_SECONDARY;
                break;
            case \MongoClient::RP_SECONDARY_PREFERRED:
                $mode = \MongoClient::RP_SECONDARY_PREFERRED;
                break;
            case \MongoClient::RP_NEAREST:
                $mode = \MongoClient::RP_NEAREST;
                break;
            default:
                trigger_error("The value '$readPreference' is not valid as read preference type", E_USER_WARNING);
                return false;
        }

        if ($readPreference == \MongoClient::RP_PRIMARY && !empty($tags)) {
            trigger_error("You can't use read preference tags with a read preference of PRIMARY", E_USER_WARNING);
            return false;
        }

        $this->readPreference = new \MongoDB\Driver\ReadPreference($mode, $tags);

        return true;
    }

    /**
     * @param array $readPreferenceArray
     * @return bool
     */
    protected function setReadPreferenceFromArray($readPreferenceArray)
    {
        $readPreference = $readPreferenceArray['type'];
        $tags = isset($readPreferenceArray['tagsets']) ? $readPreferenceArray['tagsets'] : [];

        return $this->setReadPreferenceFromParameters($readPreference, $tags);
    }

    /**
     * @param bool $ok
     * @return bool
     */
    protected function setReadPreferenceFromSlaveOkay($ok = true)
    {
        $result = $this->getSlaveOkayFromReadPreference();
        $readPreference = new \MongoDB\Driver\ReadPreference(
            // flat (Q53): the string modes, not the deprecated integer constants
            $ok ? \MongoClient::RP_SECONDARY_PREFERRED : \MongoClient::RP_PRIMARY,
            $ok ? $this->readPreference->getTagSets() : []
        );

        $this->readPreference = $readPreference;

        return $result;
    }
}
