<template>
  <v-card tile class="elevation-15">
    <v-app-bar
      app
      fixed
      color="transparent"
      dark
      clipped-left
      class="elevation-0"
    >
      <v-avatar color="primary" size="50" class="elevation-10">
        <span class="headline white--text">A</span>
      </v-avatar>
      <div class="ml-3">
        <div class="title font-weight-regular">
          Welcome,
          <span class="font-weight-bold">{{ ComputeFullName }}</span>
        </div>
      </div>
      <v-spacer />
      <div>
        <v-btn to="/Locations" outlined>Locations</v-btn>
      </div>
      <div class="hidden-xs-only">
        <v-btn v-if="$store.getters.token" outlined @click="Logout"
          >LOGOUT</v-btn
        >
      </div>
    </v-app-bar>
  </v-card>
</template>
