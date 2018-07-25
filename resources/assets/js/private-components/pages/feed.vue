<template>
    <div>
        <feed-sidebar></feed-sidebar>

        <div class="feed-content">

            <div class="row date-separated-feed-item">

                <div class="col-md-12">
                    <div class="date-title">
                        <h2>Jun 2018 - Aug 2018</h2>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="box box-primary direct-chat direct-chat-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Direct Chat</h3>

                            <div class="box-tools pull-right">
                                <span data-toggle="tooltip" title="" class="badge bg-light-blue" data-original-title="3 New Messages">3</span>
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="" data-widget="chat-pane-toggle" data-original-title="Contacts">
                                    <i class="fa fa-comments"></i></button>
                                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <!-- Conversations are loaded here -->
                            <div class="direct-chat-messages">
                                <!-- Message. Default to the left -->
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-info clearfix">
                                        <span class="direct-chat-name pull-left">Alexander Pierce</span>
                                        <span class="direct-chat-timestamp pull-right">23 Jan 2:00 pm</span>
                                    </div>
                                    <!-- /.direct-chat-info -->
                                    <img class="direct-chat-img" src="http://google.com/favicon.ico" alt="Message User Image"><!-- /.direct-chat-img -->
                                    <div class="direct-chat-text">
                                        Is this template really for free? That's unbelievable!
                                    </div>
                                    <!-- /.direct-chat-text -->
                                </div>
                                <!-- /.direct-chat-msg -->

                                <!-- Message to the right -->
                                <div class="direct-chat-msg right">
                                    <div class="direct-chat-info clearfix">
                                        <span class="direct-chat-name pull-right">Sarah Bullock</span>
                                        <span class="direct-chat-timestamp pull-left">23 Jan 2:05 pm</span>
                                    </div>
                                    <!-- /.direct-chat-info -->
                                    <img class="direct-chat-img" src="http://google.com/favicon.ico" alt="Message User Image"><!-- /.direct-chat-img -->
                                    <div class="direct-chat-text">
                                        You better believe it!
                                    </div>
                                    <!-- /.direct-chat-text -->
                                </div>
                                <!-- /.direct-chat-msg -->
                            </div>
                            <!--/.direct-chat-messages-->

                            <!-- Contacts are loaded here -->
                            <div class="direct-chat-contacts">
                                <ul class="contacts-list">
                                    <li>
                                        <a href="#">
                                            <img class="contacts-list-img" src="http://google.com/favicon.ico" alt="User Image">

                                            <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Count Dracula
                              <small class="contacts-list-date pull-right">2/28/2015</small>
                            </span>
                                                <span class="contacts-list-msg">How have you been? I was...</span>
                                            </div>
                                            <!-- /.contacts-list-info -->
                                        </a>
                                    </li>
                                    <!-- End Contact Item -->
                                </ul>
                                <!-- /.contatcts-list -->
                            </div>
                            <!-- /.direct-chat-pane -->
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <form action="#" method="post">
                                <div class="input-group">
                                    <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary btn-flat">Send</button>
                      </span>
                                </div>
                            </form>
                        </div>
                        <!-- /.box-footer-->
                    </div>
                </div>


            </div>

        </div>
    </div>
</template>

<script>
    export default {
        name: "feed"
    }
</script>

<style scoped>

</style>