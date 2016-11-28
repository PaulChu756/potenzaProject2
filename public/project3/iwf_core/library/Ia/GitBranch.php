<?php
/**
 * http://stackoverflow.com/a/29165543/421726
 */
class Ia_GitBranch
{
    /**
     * @var string
     */
    private $branch;

    const MASTER = 'master';
    const DEVELOP = 'develop';

    const HOTFIX = 'hotfix';
    const FEATURE = 'feature';

    /**
     * @param \SplFileObject $gitHeadFile
     */
    public function __construct(\SplFileObject $gitHeadFile)
    {
        $ref = explode("/", $gitHeadFile->current(), 3);

        $this->branch = rtrim($ref[2]);
    }

    /**
     * @param string $dir
     *
     * @return static
     */
    public static function createFromGitRootDir($dir)
    {
        try {
            $gitHeadFile = new \SplFileObject($dir.'/.git/HEAD', 'r');
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Directory "%s" is not a Git repository.', $dir));
        }

        return new static($gitHeadFile);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->branch;
    }

    /**
     * @return boolean
     */
    public function isBasedOnMaster()
    {
        return $this->getFlowType() === self::HOTFIX || $this->getFlowType() === self::MASTER;
    }

    /**
     * @return boolean
     */
    public function isBasedOnDevelop()
    {
        return $this->getFlowType() === self::FEATURE || $this->getFlowType() === self::DEVELOP;
    }

    /**
     * @return string
     */
    private function getFlowType()
    {
        $name = explode('/', $this->branch);

        return $name[0];
    }
}