use GitWrapper\GitException;
        parent::tearDown();
    public function testGitApply()
    {
        $git = $this->getWorkingCopy();

        $patch = <<<PATCH
diff --git a/FileCreatedByPatch.txt b/FileCreatedByPatch.txt
new file mode 100644
index 0000000..dfe437b
--- /dev/null
+++ b/FileCreatedByPatch.txt
@@ -0,0 +1 @@
+contents

PATCH;
        file_put_contents(self::WORKING_DIR . '/patch.txt', $patch);
        $git->apply('patch.txt');
        $this->assertRegExp('@\?\?\\s+FileCreatedByPatch\\.txt@s', $git->getStatus());
        $this->assertEquals("contents\n", file_get_contents(self::WORKING_DIR . '/FileCreatedByPatch.txt'));
    }

    public function testGitClean()
    {
        $git = $this->getWorkingCopy();

        file_put_contents(self::WORKING_DIR . '/untracked.file', "untracked\n");

        $result = $git
            ->clean('-d', '-f')
        ;

        $this->assertSame($git, $result);
        $this->assertFileNotExists(self::WORKING_DIR . '/untracked.file');
    }

    /**
     * This tests an odd case where sometimes even though a command fails and an exception is thrown
     * the result of Process::getErrorOutput() is empty because the output is sent to STDOUT instead of STDERR. So
     * there's a code path in GitProcess::run() to check the output from Process::getErrorOutput() and if it's empty use
     * the result from Process::getOutput() instead
     */
    public function testGitPullErrorWithEmptyErrorOutput()
    {
        $git = $this->getWorkingCopy();

        try {
            $git->commit('Nothing to commit so generates an error / not error');
        } catch(GitException $exception) {
            $errorOutput = $exception->getMessage();
        }

        $this->assertTrue(strpos($errorOutput, "Your branch is up-to-date with 'origin/master'.") !== false);
    }
