<template>
    <!-- Modal -->
    <div class="modal fade" id="feed-details-pop-up" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                    <h3 class="">
                        Found on <strong>30 Jun 2018</strong>
                    </h3>

                </div>
                <div class="modal-body">

                    <h2 class="feed-title">Viollier AG</h2>

                    <div v-html="'Hired new employee'" class="feed-text"></div>

                    <div class="buttons-block">
                        <div class="row">
                            <div class="col-md-6 with-right-border">
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-comment"></i>
                                            Comment
                                        </a>
                                    </div>

                                    <div class="col-md-4">
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-floppy-o"></i>
                                            Save
                                        </a>
                                    </div>

                                    <div class="col-md-4">
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-share"></i>
                                            Share
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <p class="commented-by">
                                    Commented by
                                    <a href="javascript:void(0)">Patrick De Boer</a>
                                    and
                                    <a href="javascript:void(0)">3 others</a>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="comments-block">

                        <h3 class="pre-title">Comments</h3>

                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "feed-details-pop-up"
    }
</script>

<style scoped>

</style>