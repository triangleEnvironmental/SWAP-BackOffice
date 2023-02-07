<?php

namespace App\Console\Commands;

use App\Models\Page;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateTermly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'termly:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pages from Termly';

    private function kill_port($port_number)
    {
        $cmd = <<<CMD
target_ports=$port_number
for port in \$target_ports
do
lsof -t -i:\$port | xargs kill -9
done
CMD;
        shell_exec($cmd);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $node_server_runner = null;
        try {
            $crawler_port = 2022;
            $this->kill_port($crawler_port);

            $descriptorspec = [
                0 => array("pipe", "r"),   // stdin is a pipe that the child will read from
                1 => array("pipe", "w"),   // stdout is a pipe that the child will write to
                2 => array("pipe", "w")    // stderr is a pipe that the child will write to
            ];
            $pipes = [];

            $node_server_runner = proc_open("node " . escapeshellarg(resource_path('crawler/node-server.js')), $descriptorspec, $pipes);

            if ($s = fgets($pipes[1])) {

                /// Terms of Service
                $success = false;
                $response = Http::timeout(200)
                    ->get("http://127.0.0.1:$crawler_port/terms");

                if ($response->ok()) {
                    $content = $response->body();
                    if ($content) {
                        Page::terms()
                            ->update([
                                'content_en' => $content,
                            ]);
                        $success = true;
                    }
                }

                if (!$success) {
                    dump("Fail to get terms of service");
                    Log::error("Fail to get terms of service");
                }

                /// Privacy Policy
                $success = false;
                $response = Http::timeout(200)
                    ->get("http://127.0.0.1:$crawler_port/policy");

                if ($response->ok()) {
                    $content = $response->body();
                    if ($content) {
                        Page::policy()
                            ->update([
                                'content_en' => $content,
                            ]);
                        $success = true;
                    }
                }

                if (!$success) {
                    dump("Fail to get privacy policy");
                    Log::error("Fail to get privacy policy");
                }
            }

            dump("Terms and Policy Updated Successfully");
            Log::info("Terms and Policy Updated Successfully");
            proc_terminate($node_server_runner);
        } catch (\Exception $exception) {
            dump($exception);
            if ($node_server_runner != null) {
                proc_terminate($node_server_runner);
                // In case server.js still running
                // run this
                // sudo kill -9 `ps aux | grep node-server.js | grep -v grep | awk '{print $2}'`
            }
        }

        return 0;
    }
}
